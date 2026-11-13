<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use App\Models\Cabang;
use App\Models\MasterBarang;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\User;

class DummyTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Memulai pembuatan data dummy transaksi selama 180 hari (6 bulan)...');
        
        // Hapus data transaksi dan detail lama agar bersih dan tidak double (Optional, bisa dinonaktifkan kalau gamau)
        // \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // Transaksi::truncate();
        // DetailTransaksi::truncate();
        // \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $cabangs = Cabang::all();
        $barangs = MasterBarang::whereIn('tipe_barang', ['Fisik ACC', 'Fisik Voucher Kartu'])->get();
        $kasir = User::where('role', 'kasir')->first();
        $kasirId = $kasir ? $kasir->id : 1;

        if ($cabangs->isEmpty() || $barangs->isEmpty()) {
            $this->command->error('Cabang atau Master Barang Fisik kosong. Harap isi terlebih dahulu.');
            return;
        }

        $startDate = Carbon::now()->subDays(180)->startOfDay();
        $endDate = Carbon::now()->subDays(1)->startOfDay();
        $totalDays = $startDate->diffInDays($endDate) + 1;

        $progressBar = $this->command->getOutput()->createProgressBar($totalDays * count($cabangs));
        $progressBar->start();

        // Loop per hari
        for ($i = 0; $i < $totalDays; $i++) {
            $currentDate = $startDate->copy()->addDays($i);

            // Loop per cabang
            foreach ($cabangs as $cabang) {
                // Generate 1 transaksi gabungan per hari per cabang
                $transaksi = Transaksi::create([
                    'kasir_id' => $kasirId,
                    'cabang_id' => $cabang->id,
                    'total_tagihan' => 0, // Akan diupdate nanti
                    'total_diskon' => 0,
                    'uang_diterima' => 0,
                    'kembalian' => 0,
                    'metode_pembayaran' => 'cash',
                    'tanggal_transaksi' => $currentDate->format('Y-m-d H:i:s'),
                    'created_at' => $currentDate,
                    'updated_at' => $currentDate,
                ]);

                $totalTagihan = 0;

                // Masukkan semua barang fisik ke dalam transaksi dengan penjualan stabil
                foreach ($barangs as $barang) {
                    // Kuantitas acak tapi stabil (rentang 8 sampai 15 pcs)
                    // Sehingga tidak ada angka 0, dan tidak ada lonjakan (anomali)
                    $qty = rand(8, 15);
                    $hargaJual = $barang->harga_jual;
                    $subtotal = $qty * $hargaJual;

                    DetailTransaksi::create([
                        'transaksi_id' => $transaksi->id,
                        'barang_id' => $barang->id,
                        'harga_jual_final' => $hargaJual,
                        'kuantitas' => $qty,
                        'hpp_satuan' => $barang->harga_modal,
                        'jenis_item' => 'Fisik',
                        'diskon' => 0,
                        'created_at' => $currentDate,
                        'updated_at' => $currentDate,
                    ]);

                    $totalTagihan += $subtotal;
                }

                // Update total tagihan dan uang diterima
                $transaksi->update([
                    'total_tagihan' => $totalTagihan,
                    'uang_diterima' => $totalTagihan, // Pas uang pas
                ]);

                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->command->info("\nSeeder sukses! Berhasil menambahkan data transaksi stabil selama 180 hari terakhir.");
    }
}
