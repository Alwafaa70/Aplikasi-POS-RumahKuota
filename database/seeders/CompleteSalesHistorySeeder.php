<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\DataHistorisAgregasi;
use App\Models\MasterBarang;
use App\Models\Cabang;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class CompleteSalesHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('=== MEMULAI GENERASI DATA HISTORIS PENJUALAN 6 BULAN (180 HARI) ===');

        $cabangs = Cabang::all();
        $barangFisik = MasterBarang::whereIn('tipe_barang', ['Fisik ACC', 'Fisik Voucher Kartu'])->get();
        $kasirDefault = User::where('role', 'kasir')->first() ?: User::first();
        $kasirIdDefault = $kasirDefault ? $kasirDefault->id : 1;

        if ($cabangs->isEmpty() || $barangFisik->isEmpty()) {
            $this->command->error('Cabang atau Master Barang kosong!');
            return;
        }

        // Mapping profil kecepatan produk (Base Qty Harian per Cabang)
        $productProfiles = [
            // Fast-Moving (Voucher Data Populer)
            4  => ['type' => 'fast',   'base' => 14, 'name' => 'Tsel 8Gb/14Hari'],
            5  => ['type' => 'fast',   'base' => 13, 'name' => 'Indosat'],
            6  => ['type' => 'fast',   'base' => 15, 'name' => 'XL 7GB/30Hari'],
            13 => ['type' => 'fast',   'base' => 11, 'name' => 'Telkomsel'],
            23 => ['type' => 'fast',   'base' => 12, 'name' => 'isat 10GB/30hari'],

            // Medium-Moving (Aksesoris Esensial & Kartu Reguler)
            3  => ['type' => 'medium', 'base' => 5,  'name' => 'Charger 33Watt'],
            14 => ['type' => 'medium', 'base' => 4,  'name' => 'Power Bank 10.000'],
            15 => ['type' => 'medium', 'base' => 6,  'name' => 'Tempered Glass A4'],

            // Slow-Moving (Aksesoris Segmented / Spesifik)
            22 => ['type' => 'slow',   'base' => 2,  'name' => 'cashing A43'],
            24 => ['type' => 'slow',   'base' => 2,  'name' => 'KD IP Robbot'],
        ];

        // Rentang 180 hari (6 Bulan Penuh: Mei - Oktober 2026)
        $startDate = Carbon::parse('2026-05-01')->startOfDay();
        $endDate   = Carbon::parse('2026-10-27')->endOfDay();
        $totalDays = $startDate->diffInDays($endDate) + 1;

        $this->command->info("Menghapus data transaksi lama pada rentang {$startDate->format('Y-m-d')} s/d {$endDate->format('Y-m-d')}...");

        DB::beginTransaction();
        try {
            // Hapus detail dan transaksi lama pada rentang ini
            $oldTrxIds = Transaksi::whereBetween('tanggal_transaksi', [
                $startDate->format('Y-m-d H:i:s'),
                $endDate->format('Y-m-d H:i:s')
            ])->pluck('id');

            if ($oldTrxIds->isNotEmpty()) {
                DetailTransaksi::whereIn('transaksi_id', $oldTrxIds)->delete();
                Transaksi::whereIn('id', $oldTrxIds)->delete();
            }

            $this->command->info("Men-generate transaksi harian untuk {$cabangs->count()} cabang & {$barangFisik->count()} produk fisik ({$totalDays} hari)...");

            $trxBatch = [];
            $detailBatch = [];
            $trxCounter = 0;
            $closedDaysCounter = 0;

            for ($d = 0; $d < $totalDays; $d++) {
                $currentDate = $startDate->copy()->addDays($d);
                $dateStr = $currentDate->format('Y-m-d');
                $isSunday = $currentDate->isSunday();
                $isWeekend = $currentDate->isWeekend();

                foreach ($cabangs as $cabang) {
                    // Cek Skenario TOKO LIBUR / TUTUP (Zero Sales):
                    // 1. Libur Nasional Idul Adha (17-18 Juni 2026 - Semua Cabang Tutup)
                    // 2. Libur Nasional Kemerdekaan (17 Agustus 2026 - Semua Cabang Tutup)
                    // 3. Libur Mingguan Rutin Cabang Tertentu: Cabang 7 (Nako) & Cabang 9 (Baruc) tutup tiap hari Minggu
                    // 4. Libur Khusus Stock Opname / Renovasi: Cabang 5 (Bojongpicung) tutup pada 10-11 September 2026
                    $isStoreClosed = false;

                    if ($dateStr === '2026-06-17' || $dateStr === '2026-06-18') {
                        $isStoreClosed = true; // Idul Adha
                    } elseif ($dateStr === '2026-08-17') {
                        $isStoreClosed = true; // 17 Agustus
                    } elseif (in_array($cabang->id, [7, 9]) && $isSunday) {
                        $isStoreClosed = true; // Libur Rutin Minggu
                    } elseif ($cabang->id == 5 && ($dateStr === '2026-09-10' || $dateStr === '2026-09-11')) {
                        $isStoreClosed = true; // Stock Opname
                    }

                    if ($isStoreClosed) {
                        $closedDaysCounter++;
                        continue; // Lewati hari ini, penjualan = 0!
                    }

                    // Buat 1 transaksi harian konsolidasi per cabang
                    $trxTime = $currentDate->copy()->addHours(rand(10, 20))->addMinutes(rand(0, 59));
                    $kasirId = User::where('role', 'kasir')->where('cabang_id', $cabang->id)->value('id') ?? $kasirIdDefault;

                    $transaksiId = DB::table('transaksi')->insertGetId([
                        'kasir_id' => $kasirId,
                        'cabang_id' => $cabang->id,
                        'total_tagihan' => 0, // diupdate setelah detail dihitung
                        'total_diskon' => 0,
                        'uang_diterima' => 0,
                        'kembalian' => 0,
                        'metode_pembayaran' => rand(1, 100) <= 65 ? 'cash' : 'qris',
                        'tanggal_transaksi' => $trxTime->format('Y-m-d H:i:s'),
                        'created_at' => $trxTime->format('Y-m-d H:i:s'),
                        'updated_at' => $trxTime->format('Y-m-d H:i:s'),
                    ]);

                    $totalTagihanTrx = 0;

                    foreach ($barangFisik as $barang) {
                        $profile = $productProfiles[$barang->id] ?? ['type' => 'medium', 'base' => 4];
                        $baseQty = $profile['base'];

                        // Faktor variasi weekend: lonjakan 20% - 35% pada Sabtu & Minggu
                        $weekendMultiplier = $isWeekend ? (1 + (rand(20, 35) / 100)) : 1.0;
                        
                        // Fluktuasi acak harian (+- 15%)
                        $fluctuation = 1 + (rand(-15, 15) / 100);

                        $qty = (int) round($baseQty * $weekendMultiplier * $fluctuation);
                        $qty = max(1, $qty);

                        $hargaJual = $barang->harga_jual > 0 ? $barang->harga_jual : 25000;
                        $hargaModal = $barang->harga_modal > 0 ? $barang->harga_modal : $hargaJual * 0.75;
                        $subtotal = $qty * $hargaJual;

                        $detailBatch[] = [
                            'transaksi_id' => $transaksiId,
                            'barang_id' => $barang->id,
                            'harga_jual_final' => $hargaJual,
                            'kuantitas' => $qty,
                            'hpp_satuan' => $hargaModal,
                            'jenis_item' => 'Fisik',
                            'diskon' => 0,
                            'created_at' => $trxTime->format('Y-m-d H:i:s'),
                            'updated_at' => $trxTime->format('Y-m-d H:i:s'),
                        ];

                        $totalTagihanTrx += $subtotal;

                        if (count($detailBatch) >= 500) {
                            DB::table('detail_transaksi')->insert($detailBatch);
                            $detailBatch = [];
                        }
                    }

                    // Update total transaksi
                    DB::table('transaksi')->where('id', $transaksiId)->update([
                        'total_tagihan' => $totalTagihanTrx,
                        'uang_diterima' => $totalTagihanTrx,
                    ]);

                    $trxCounter++;
                }
            }

            // Flush remaining details
            if (!empty($detailBatch)) {
                DB::table('detail_transaksi')->insert($detailBatch);
                $detailBatch = [];
            }

            $this->command->info("Berhasil membuat {$trxCounter} transaksi harian. Tercatat {$closedDaysCounter} kejadian hari toko libur (zero sales).");

            // 2. SINKRONISASI KE DATA HISTORIS AGREGASI (6 Bulan: Mei - Okt 2026)
            $this->command->info('Menyinkronkan data agregasi bulanan ke data_historis_agregasi...');

            $months = ['2026-05', '2026-06', '2026-07', '2026-08', '2026-09', '2026-10'];

            // Bersihkan data agregasi lama untuk bulan-bulan ini agar tidak duplikat
            DataHistorisAgregasi::whereIn('periode_bulan', $months)->delete();

            // Hitung agregasi riil dari detail_transaksi join transaksi
            $agregasiData = DB::table('detail_transaksi')
                ->join('transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')
                ->whereBetween('transaksi.tanggal_transaksi', [
                    $startDate->format('Y-m-d 00:00:00'),
                    $endDate->format('Y-m-d 23:59:59')
                ])
                ->select(
                    'transaksi.cabang_id',
                    'detail_transaksi.barang_id',
                    DB::raw("strftime('%Y-%m', transaksi.tanggal_transaksi) as periode_bulan"),
                    DB::raw('SUM(detail_transaksi.kuantitas) as total_terjual')
                )
                ->groupBy('transaksi.cabang_id', 'detail_transaksi.barang_id', 'periode_bulan')
                ->get();

            $agregasiInsert = [];
            $now = Carbon::now();

            foreach ($agregasiData as $row) {
                $agregasiInsert[] = [
                    'cabang_id' => $row->cabang_id,
                    'barang_id' => $row->barang_id,
                    'periode_bulan' => $row->periode_bulan,
                    'total_unit_terjual' => (int) $row->total_terjual,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($agregasiInsert) >= 200) {
                    DB::table('data_historis_agregasi')->insert($agregasiInsert);
                    $agregasiInsert = [];
                }
            }

            if (!empty($agregasiInsert)) {
                DB::table('data_historis_agregasi')->insert($agregasiInsert);
            }

            $totalAgregasi = DataHistorisAgregasi::whereIn('periode_bulan', $months)->count();
            $this->command->info("Data agregasi berhasil diperbarui: {$totalAgregasi} kombinasi cabang & barang tersimpan lengkap!");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Gagal men-generate data historis: " . $e->getMessage());
            throw $e;
        }

        // 3. JALANKAN OTOMATIS AUTO-CALCULATE EWS
        $this->command->info('Menjalankan ews:auto-calculate untuk memperbarui parameter Buffer EWS & SES...');
        Artisan::call('ews:auto-calculate');
        $this->command->info('ews:auto-calculate selesai dijalankan!');

        $this->command->info('=== SELURUH DATA HISTORIS & BUFFER EWS BERHASIL DIMATANGKAN 100%! ===');
    }
}
