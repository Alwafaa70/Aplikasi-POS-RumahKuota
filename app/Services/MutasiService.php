<?php

namespace App\Services;

use App\Models\MutasiStok;
use App\Models\DetailMutasi;
use App\Models\MutasiUang;
use App\Models\KasPusat;
use App\Models\KasCabang;
use App\Models\StokPusat;
use App\Models\StokCabang;
use App\Models\SaldoApkLayanan;
use Illuminate\Support\Facades\DB;

class MutasiService
{
    /**
     * Inisiasi pengiriman barang fisik (Gudang -> Cabang atau Cabang -> Cabang).
     * 
     * @param int $adminId
     * @param int|null $cabangAsalId null berarti dari Gudang Pusat
     * @param int $cabangTujuanId
     * @param array $items [barang_fisik_id => qty]
     */
    public function kirimBarangFisik(int $adminId, ?int $cabangAsalId, int $cabangTujuanId, array $items)
    {
        return DB::transaction(function () use ($adminId, $cabangAsalId, $cabangTujuanId, $items) {
            $status = is_null($cabangAsalId) ? 'dalam_perjalanan' : 'menunggu_konfirmasi_asal';
            
            $mutasi = MutasiStok::create([
                'admin_id'         => $adminId,
                'cabang_asal_id'   => $cabangAsalId,
                'cabang_tujuan_id' => $cabangTujuanId,
                'status'           => $status,
                'tanggal_mutasi'   => now()->toDateString(),
            ]);

            foreach ($items as $barangId => $qty) {
                if ($qty <= 0) continue;

                if (is_null($cabangAsalId)) {
                    // MUTASI DARI PUSAT -> CABANG (GUNAKAN FIFO BARANG MASUK)
                    $stokAsal = StokPusat::where('barang_id', $barangId)->lockForUpdate()->first();
                    if (!$stokAsal || $stokAsal->jumlah_stok < $qty) {
                        throw new \Exception("Stok gudang pusat tidak cukup untuk barang ID {$barangId}");
                    }
                    $stokAsal->decrement('jumlah_stok', $qty);

                    // FIFO Logic: Tarik dari batch terlama
                    $sisaKebutuhan = $qty;
                    $batches = \App\Models\BarangMasuk::where('barang_fisik_id', $barangId)
                                                      ->where('stok_sisa', '>', 0)
                                                      ->orderBy('created_at', 'asc')
                                                      ->lockForUpdate()
                                                      ->get();

                    foreach ($batches as $batch) {
                        if ($sisaKebutuhan <= 0) break;

                        $qtyAmbil = min($batch->stok_sisa, $sisaKebutuhan);
                        $batch->decrement('stok_sisa', $qtyAmbil);
                        $sisaKebutuhan -= $qtyAmbil;

                        $hppSatuan = max(0, $batch->total_harga_beli / max(1, $batch->qty_beli));

                        DetailMutasi::create([
                            'mutasi_id' => $mutasi->id,
                            'barang_id' => $barangId,
                            'kuantitas' => $qtyAmbil,
                            'barang_masuk_id' => $batch->id,
                            'hpp_satuan' => $hppSatuan
                        ]);
                    }

                    if ($sisaKebutuhan > 0) {
                         // Fallback keamanan jika data stok batch tidak sinkron dengan StokPusat
                         DetailMutasi::create([
                            'mutasi_id' => $mutasi->id,
                            'barang_id' => $barangId,
                            'kuantitas' => $sisaKebutuhan,
                            'barang_masuk_id' => null,
                            'hpp_satuan' => 0
                        ]);
                    }

                } else {
                    // MUTASI DARI CABANG -> CABANG
                    // Cek ketersediaan stok saja tanpa memotong
                    $stokAsal = StokCabang::where('cabang_id', $cabangAsalId)
                                          ->where('barang_id', $barangId)
                                          ->first();
                    if (!$stokAsal || $stokAsal->jumlah_stok < $qty) {
                        throw new \Exception("Stok cabang asal tidak cukup untuk barang ID {$barangId}");
                    }

                    // Hanya simpan detail mutasi, pemotongan stok & batch dilakukan saat konfirmasi cabang asal
                    DetailMutasi::create([
                        'mutasi_id' => $mutasi->id,
                        'barang_id' => $barangId,
                        'kuantitas' => $qty, // We will use 'kuantitas' for the requested amount, and split to batch when confirmed
                        'barang_masuk_id' => null,
                        'hpp_satuan' => 0
                    ]);
                }
            }

            return $mutasi;
        });
    }

    /**
     * Penerimaan barang fisik di tujuan.
     */
    public function terimaBarangFisik(int $mutasiId)
    {
        return DB::transaction(function () use ($mutasiId) {
            $mutasi = MutasiStok::with('detailMutasi')->lockForUpdate()->findOrFail($mutasiId);

            if (!in_array($mutasi->status, ['dikirim', 'dalam_perjalanan'])) {
                throw new \Exception("Mutasi ini tidak dalam status dikirim/dalam_perjalanan.");
            }

            foreach ($mutasi->detailMutasi as $detail) {
                // Update total stok cabang
                $stokTujuan = StokCabang::firstOrCreate(
                    ['cabang_id' => $mutasi->cabang_tujuan_id, 'barang_id' => $detail->barang_id],
                    ['jumlah_stok' => 0]
                );
                $stokTujuan->increment('jumlah_stok', $detail->kuantitas);

                // Update stok cabang batch
                if ($detail->barang_masuk_id) {
                    $stokBatch = \App\Models\StokCabangBatch::firstOrCreate([
                        'cabang_id' => $mutasi->cabang_tujuan_id,
                        'barang_id' => $detail->barang_id,
                        'barang_masuk_id' => $detail->barang_masuk_id,
                    ], [
                        'stok_sisa_cabang' => 0,
                        'hpp_batch' => $detail->hpp_satuan
                    ]);

                    $stokBatch->increment('stok_sisa_cabang', $detail->kuantitas);
                }
            }

            $mutasi->update(['status' => 'diterima']);
            return $mutasi;
        });
    }

    /**
     * Penerimaan barang fisik bersyarat (dengan pelaporan hilang/rusak).
     * @param array $items [ [ 'detail_id' => x, 'baik' => y, 'rusak' => z, 'hilang' => w, 'catatan' => '..' ] ]
     */
    public function terimaBarangBersyarat(int $mutasiId, array $items, $catatanPenerimaan = null, $buktiGambar = null, $userId = null)
    {
        return DB::transaction(function () use ($mutasiId, $items, $catatanPenerimaan, $buktiGambar, $userId) {
            $mutasi = MutasiStok::with('detailMutasi')->lockForUpdate()->findOrFail($mutasiId);

            if (!in_array($mutasi->status, ['dikirim', 'dalam_perjalanan'])) {
                throw new \Exception("Mutasi ini tidak dalam status dikirim/dalam_perjalanan.");
            }

            $hasDiscrepancy = false;

            // Mapping input items by barang_id
            $inputMapped = [];
            foreach ($items as $item) {
                $inputMapped[$item['barang_id']] = $item;
            }

            // Group details by barang_id to distribute the inputs
            $groupedDetails = $mutasi->detailMutasi->groupBy('barang_id');

            foreach ($groupedDetails as $barangId => $details) {
                if (!isset($inputMapped[$barangId])) {
                    throw new \Exception("Data penerimaan tidak lengkap untuk barang ID {$barangId}");
                }

                $input = $inputMapped[$barangId];
                
                // Validate totals for this barang_id
                $totalInput = $input['baik'] + $input['rusak'] + $input['hilang'];
                $totalKuantitas = $details->sum('kuantitas');

                if ($totalInput != $totalKuantitas) {
                    throw new \Exception("Total kuantitas (baik + rusak + hilang = {$totalInput}) tidak sama dengan total dikirim ({$totalKuantitas}) untuk barang ID {$barangId}");
                }

                if ($input['rusak'] > 0 || $input['hilang'] > 0) {
                    $hasDiscrepancy = true;
                }

                // Sisa yang harus didistribusikan
                $sisaBaik = $input['baik'];
                $sisaRusak = $input['rusak'];
                $sisaHilang = $input['hilang'];
                $catatan = $input['catatan'] ?? null;

                foreach ($details as $detail) {
                    $alokasiBaik = min($sisaBaik, $detail->kuantitas);
                    $sisaBaik -= $alokasiBaik;
                    $sisaKuantitas = $detail->kuantitas - $alokasiBaik;

                    $alokasiRusak = min($sisaRusak, $sisaKuantitas);
                    $sisaRusak -= $alokasiRusak;
                    $sisaKuantitas -= $alokasiRusak;

                    $alokasiHilang = min($sisaHilang, $sisaKuantitas);
                    $sisaHilang -= $alokasiHilang;

                    // Update detail_mutasi
                    $detail->update([
                        'qty_diterima_baik' => $alokasiBaik,
                        'qty_rusak'         => $alokasiRusak,
                        'qty_hilang'        => $alokasiHilang,
                        'catatan_penerima'  => $catatan
                    ]);

                    // Hanya qty_baik yang masuk ke stok cabang
                    if ($alokasiBaik > 0) {
                        // Update total stok cabang
                        $stokTujuan = StokCabang::firstOrCreate(
                            ['cabang_id' => $mutasi->cabang_tujuan_id, 'barang_id' => $detail->barang_id],
                            ['jumlah_stok' => 0]
                        );
                        $stokTujuan->increment('jumlah_stok', $alokasiBaik);

                        // Update stok cabang batch
                        if ($detail->barang_masuk_id) {
                            $stokBatch = \App\Models\StokCabangBatch::firstOrCreate([
                                'cabang_id' => $mutasi->cabang_tujuan_id,
                                'barang_id' => $detail->barang_id,
                                'barang_masuk_id' => $detail->barang_masuk_id,
                            ], [
                                'stok_sisa_cabang' => 0,
                                'hpp_batch' => $detail->hpp_satuan
                            ]);

                            $stokBatch->increment('stok_sisa_cabang', $alokasiBaik);
                        }
                    }
                }
            }

            $statusPenerimaan = $hasDiscrepancy ? 'bermasalah' : 'sesuai';

            $mutasi->update([
                'status' => 'diterima',
                'status_penerimaan' => $statusPenerimaan,
                'catatan_penerimaan' => $catatanPenerimaan,
                'bukti_tujuan' => $buktiGambar,
                'keterangan_tujuan' => $catatanPenerimaan,
                'waktu_konfirmasi_tujuan' => now(),
                'user_konfirmasi_tujuan_id' => $userId,
            ]);
            
            return $mutasi;
        });
    }

    public function konfirmasiAsalBarang(int $mutasiId, int $userId, $keterangan = null, $buktiGambar = null)
    {
        return DB::transaction(function () use ($mutasiId, $userId, $keterangan, $buktiGambar) {
            $mutasi = MutasiStok::with('detailMutasi')->lockForUpdate()->findOrFail($mutasiId);

            if ($mutasi->status !== 'menunggu_konfirmasi_asal') {
                throw new \Exception("Mutasi tidak menunggu konfirmasi asal.");
            }

            // Potong stok dan alokasikan batch
            foreach ($mutasi->detailMutasi as $detail) {
                $barangId = $detail->barang_id;
                $qty = $detail->kuantitas;

                $stokAsal = StokCabang::where('cabang_id', $mutasi->cabang_asal_id)
                                      ->where('barang_id', $barangId)
                                      ->lockForUpdate()
                                      ->first();
                
                if (!$stokAsal || $stokAsal->jumlah_stok < $qty) {
                    throw new \Exception("Stok cabang asal tidak cukup untuk barang ID {$barangId}");
                }
                $stokAsal->decrement('jumlah_stok', $qty);

                $sisaKebutuhan = $qty;
                $batches = \App\Models\StokCabangBatch::where('cabang_id', $mutasi->cabang_asal_id)
                                                      ->where('barang_id', $barangId)
                                                      ->where('stok_sisa_cabang', '>', 0)
                                                      ->orderBy('created_at', 'asc')
                                                      ->lockForUpdate()
                                                      ->get();
                
                $isFirstBatch = true;
                foreach ($batches as $batch) {
                    if ($sisaKebutuhan <= 0) break;

                    $qtyAmbil = min($batch->stok_sisa_cabang, $sisaKebutuhan);
                    $batch->decrement('stok_sisa_cabang', $qtyAmbil);
                    $sisaKebutuhan -= $qtyAmbil;

                    if ($isFirstBatch) {
                        $detail->update([
                            'barang_masuk_id' => $batch->barang_masuk_id,
                            'hpp_satuan' => $batch->hpp_batch,
                            'kuantitas' => $qtyAmbil
                        ]);
                        $isFirstBatch = false;
                    } else {
                        DetailMutasi::create([
                            'mutasi_id' => $mutasi->id,
                            'barang_id' => $barangId,
                            'kuantitas' => $qtyAmbil,
                            'barang_masuk_id' => $batch->barang_masuk_id,
                            'hpp_satuan' => $batch->hpp_batch
                        ]);
                    }
                }

                if ($sisaKebutuhan > 0) {
                    if ($isFirstBatch) {
                         $detail->update(['kuantitas' => $sisaKebutuhan]);
                    } else {
                         DetailMutasi::create([
                            'mutasi_id' => $mutasi->id,
                            'barang_id' => $barangId,
                            'kuantitas' => $sisaKebutuhan,
                            'barang_masuk_id' => null,
                            'hpp_satuan' => 0
                        ]);
                    }
                }
            }

            $mutasi->update([
                'status' => 'dalam_perjalanan',
                'bukti_asal' => $buktiGambar,
                'keterangan_asal' => $keterangan,
                'waktu_konfirmasi_asal' => now(),
                'user_konfirmasi_asal_id' => $userId,
            ]);

            return $mutasi;
        });
    }

    public function tolakAsalBarang(int $mutasiId, int $userId, string $alasan)
    {
        return DB::transaction(function () use ($mutasiId, $userId, $alasan) {
            $mutasi = MutasiStok::lockForUpdate()->findOrFail($mutasiId);

            if ($mutasi->status !== 'menunggu_konfirmasi_asal') {
                throw new \Exception("Mutasi tidak menunggu konfirmasi asal.");
            }

            $mutasi->update([
                'status' => 'ditolak_asal',
                'alasan_tolak' => $alasan,
                'waktu_konfirmasi_asal' => now(),
                'user_konfirmasi_asal_id' => $userId,
            ]);

            return $mutasi;
        });
    }

    /**
     * Inisiasi pengiriman Cash Fisik.
     */
    public function kirimCashFisik(int $adminId, ?int $cabangAsalId, int $cabangTujuanId, float $nominal, $keterangan = null)
    {
        return DB::transaction(function () use ($adminId, $cabangAsalId, $cabangTujuanId, $nominal, $keterangan) {
            if ($nominal <= 0) {
                throw new \Exception("Nominal mutasi harus lebih besar dari 0.");
            }

            $status = is_null($cabangAsalId) ? 'dalam_perjalanan' : 'menunggu_konfirmasi_asal';

            if (is_null($cabangAsalId)) {
                $kas = KasPusat::lockForUpdate()->first();
                if (!$kas || $kas->saldo < $nominal) {
                    throw new \Exception("Kas Pusat tidak mencukupi untuk mutasi ini.");
                }
                $kas->decrement('saldo', $nominal);
            } else {
                $kas = KasCabang::where('cabang_id', $cabangAsalId)->first();
                if (!$kas || $kas->saldo < $nominal) {
                    throw new \Exception("Kas Cabang asal tidak mencukupi untuk mutasi ini.");
                }
                // Saldo tidak dipotong di sini, melainkan saat cabang asal mengkonfirmasi
            }

            return MutasiUang::create([
                'admin_id'         => $adminId,
                'cabang_asal_id'   => $cabangAsalId,
                'cabang_tujuan_id' => $cabangTujuanId,
                'jenis_mutasi'     => 'cash',
                'nominal'          => $nominal,
                'status'           => $status,
                'keterangan'       => $keterangan,
            ]);
        });
    }

    /**
     * Penerimaan Cash Fisik di tujuan.
     */
    public function terimaCashFisik(int $mutasiId, $userId = null)
    {
        return DB::transaction(function () use ($mutasiId, $userId) {
            $mutasi = MutasiUang::where('jenis_mutasi', 'cash')->lockForUpdate()->findOrFail($mutasiId);

            if (!in_array($mutasi->status, ['dikirim', 'dalam_perjalanan'])) {
                throw new \Exception("Mutasi uang ini tidak dalam status dikirim/dalam_perjalanan.");
            }

            $kasTujuan = KasCabang::firstOrCreate(
                ['cabang_id' => $mutasi->cabang_tujuan_id],
                ['saldo' => 0]
            );
            $kasTujuan->increment('saldo', $mutasi->nominal);

            $mutasi->update([
                'status' => 'diterima',
                'waktu_konfirmasi_tujuan' => now(),
                'user_konfirmasi_tujuan_id' => $userId,
            ]);
            return $mutasi;
        });
    }

    public function konfirmasiAsalCash(int $mutasiId, int $userId, $keterangan = null, $buktiGambar = null)
    {
        return DB::transaction(function () use ($mutasiId, $userId, $keterangan, $buktiGambar) {
            $mutasi = MutasiUang::lockForUpdate()->findOrFail($mutasiId);

            if ($mutasi->status !== 'menunggu_konfirmasi_asal') {
                throw new \Exception("Mutasi tidak menunggu konfirmasi asal.");
            }

            // Potong saldo asal
            $kasAsal = KasCabang::where('cabang_id', $mutasi->cabang_asal_id)->lockForUpdate()->first();
            if (!$kasAsal || $kasAsal->saldo < $mutasi->nominal) {
                throw new \Exception("Kas Cabang asal tidak mencukupi untuk mutasi ini.");
            }
            $kasAsal->decrement('saldo', $mutasi->nominal);

            $mutasi->update([
                'status' => 'dalam_perjalanan',
                'bukti_asal' => $buktiGambar,
                'keterangan_asal' => $keterangan,
                'waktu_konfirmasi_asal' => now(),
                'user_konfirmasi_asal_id' => $userId,
            ]);

            return $mutasi;
        });
    }

    public function tolakAsalCash(int $mutasiId, int $userId, string $alasan)
    {
        return DB::transaction(function () use ($mutasiId, $userId, $alasan) {
            $mutasi = MutasiUang::lockForUpdate()->findOrFail($mutasiId);

            if ($mutasi->status !== 'menunggu_konfirmasi_asal') {
                throw new \Exception("Mutasi tidak menunggu konfirmasi asal.");
            }

            $mutasi->update([
                'status' => 'ditolak_asal',
                'alasan_tolak' => $alasan,
                'waktu_konfirmasi_asal' => now(),
                'user_konfirmasi_asal_id' => $userId,
            ]);

            return $mutasi;
        });
    }

    /**
     * Transfer Saldo Digital secara instan.
     * Hanya dari Pusat -> Cabang.
     */
    public function transferSaldoDigital(int $adminId, int $cabangTujuanId, int $apkSumberId, int $apkTujuanId, float $nominal, float $feeAdmin, $keterangan = null)
    {
        return DB::transaction(function () use ($adminId, $cabangTujuanId, $apkSumberId, $apkTujuanId, $nominal, $feeAdmin, $keterangan) {
            $totalPotongan = $nominal + $feeAdmin;

            // Potong saldo apk pusat (cabang_id = null)
            $saldoSumber = SaldoApkLayanan::where('cabang_id', null)
                                          ->where('apk_layanan_id', $apkSumberId)
                                          ->lockForUpdate()
                                          ->first();

            if (!$saldoSumber || $saldoSumber->saldo < $totalPotongan) {
                throw new \Exception("Saldo Apk Sumber (Pusat) tidak mencukupi (dibutuhkan: {$totalPotongan}).");
            }
            $saldoSumber->decrement('saldo', $totalPotongan);

            // Tambah saldo apk cabang
            $saldoTujuan = SaldoApkLayanan::firstOrCreate(
                ['cabang_id' => $cabangTujuanId, 'apk_layanan_id' => $apkTujuanId],
                ['saldo' => 0]
            );
            $saldoTujuan->increment('saldo', $nominal);

            // Mutasi digital bersifat instan (langsung 'diterima')
            return MutasiUang::create([
                'admin_id'         => $adminId,
                'cabang_asal_id'   => null,
                'cabang_tujuan_id' => $cabangTujuanId,
                'jenis_mutasi'     => 'saldo_digital',
                'apk_sumber_id'    => $apkSumberId,
                'apk_tujuan_id'    => $apkTujuanId,
                'nominal'          => $nominal,
                'fee_admin'        => $feeAdmin,
                'status'           => 'diterima',
                'keterangan'       => $keterangan,
            ]);
        });
    }
}
