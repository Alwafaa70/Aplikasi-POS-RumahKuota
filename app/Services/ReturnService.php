<?php

namespace App\Services;

use App\Models\PembelianSupplier;
use App\Models\ReturPembelian;
use App\Models\DetailRetur;
use App\Models\StokPusat;
use App\Models\BarangMasuk;
use App\Models\DetailPembelian;
use App\Models\KasPusat;
use App\Models\SaldoApkLayanan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReturnService
{
    /**
     * Mengajukan retur barang rusak ke supplier.
     * Mengurangi stok fisik dan stok_sisa di barang_masuk.
     *
     * @param int $pembelianId ID dari PembelianSupplier yang sudah 'selesai'
     * @param array $items Array dari barang_fisik_id => qty_retur
     */
    public function ajukanRetur(int $pembelianId, array $items)
    {
        return DB::transaction(function () use ($pembelianId, $items) {
            $pembelian = PembelianSupplier::findOrFail($pembelianId);

            if ($pembelian->status !== 'selesai') {
                throw new \Exception("Hanya pembelian yang sudah selesai yang dapat diretur.");
            }

            $retur = ReturPembelian::create([
                'admin_id'      => Auth::id(),
                'pembelian_id'  => $pembelianId,
                'status'        => 'menunggu_solusi',
                'tanggal_retur' => now()->toDateString(),
            ]);

            foreach ($items as $barangId => $qtyRetur) {
                if ($qtyRetur <= 0) continue;

                // Validasi jumlah retur tidak melebihi yang dibeli
                $detailBeli = DetailPembelian::where('pembelian_id', $pembelianId)
                                ->where('barang_fisik_id', $barangId)
                                ->first();

                if (!$detailBeli || $qtyRetur > $detailBeli->qty) {
                    throw new \Exception("Kuantitas retur melebihi jumlah pembelian untuk barang ID {$barangId}.");
                }

                // 1. Kurangi stok gudang (Stok Pusat)
                $stokPusat = StokPusat::where('barang_id', $barangId)->lockForUpdate()->first();
                if (!$stokPusat || $stokPusat->jumlah_stok < $qtyRetur) {
                    throw new \Exception("Stok gudang tidak mencukupi untuk melakukan retur barang ID {$barangId}.");
                }
                $stokPusat->decrement('jumlah_stok', $qtyRetur);

                // 2. Kurangi stok_sisa di batch BarangMasuk yang terkait
                $barangMasuk = BarangMasuk::where('pembelian_id', $pembelianId)
                                ->where('barang_fisik_id', $barangId)
                                ->lockForUpdate()
                                ->first();
                
                if ($barangMasuk) {
                    if ($barangMasuk->stok_sisa < $qtyRetur) {
                        // Terjadi jika barang dari batch ini sudah didistribusikan ke cabang
                        throw new \Exception("Stok dari batch pembelian ini sudah tidak cukup (sudah terdistribusi) untuk barang ID {$barangId}.");
                    }
                    $barangMasuk->decrement('stok_sisa', $qtyRetur);
                }

                // 3. Simpan Detail Retur
                DetailRetur::create([
                    'retur_id'        => $retur->id,
                    'barang_fisik_id' => $barangId,
                    'qty_retur'       => $qtyRetur,
                    'keterangan'      => 'Barang Rusak',
                ]);
            }

            return $retur;
        });
    }

    /**
     * Menyelesaikan retur (Kirim Ulang / Refund).
     *
     * @param int $returId
     * @param string $solusi 'kirim_ulang' atau 'refund'
     */
    public function selesaikanRetur(int $returId, string $solusi)
    {
        return DB::transaction(function () use ($returId, $solusi) {
            $retur = ReturPembelian::with('detailRetur', 'pembelian')->lockForUpdate()->findOrFail($returId);

            if ($retur->status !== 'menunggu_solusi') {
                throw new \Exception("Retur ini sudah diselesaikan.");
            }

            if ($solusi === 'kirim_ulang') {
                // Supplier mengirim ulang barang pengganti
                // Tambahkan kembali stok pusat & stok sisa barang_masuk
                foreach ($retur->detailRetur as $detail) {
                    StokPusat::where('barang_id', $detail->barang_fisik_id)
                             ->increment('jumlah_stok', $detail->qty_retur);

                    BarangMasuk::where('pembelian_id', $retur->pembelian_id)
                               ->where('barang_fisik_id', $detail->barang_fisik_id)
                               ->increment('stok_sisa', $detail->qty_retur);
                }
            } elseif ($solusi === 'refund') {
                // Supplier mengembalikan uang
                // Hitung total refund
                $totalRefund = 0;
                foreach ($retur->detailRetur as $detail) {
                    $detailBeli = DetailPembelian::where('pembelian_id', $retur->pembelian_id)
                                    ->where('barang_fisik_id', $detail->barang_fisik_id)
                                    ->first();
                    
                    if ($detailBeli) {
                        $totalRefund += ($detail->qty_retur * $detailBeli->harga_satuan);
                    }
                }

                $pembelian = $retur->pembelian;

                // Kembalikan ke saldo awal saat pembelian
                if ($pembelian->metode_pembayaran === 'cash_pusat') {
                    $kasPusat = KasPusat::firstOrCreate([], ['saldo' => 0]);
                    $kasPusat->increment('saldo', $totalRefund);
                } else {
                    $saldoApk = SaldoApkLayanan::where('cabang_id', null)
                                    ->where('apk_layanan_id', $pembelian->apk_layanan_id)
                                    ->lockForUpdate()
                                    ->first();
                    if ($saldoApk) {
                        $saldoApk->increment('saldo', $totalRefund);
                    }
                }
            } else {
                throw new \Exception("Solusi retur tidak valid.");
            }

            $retur->update(['status' => $solusi]);

            return $retur;
        });
    }
}
