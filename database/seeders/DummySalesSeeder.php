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
use Illuminate\Support\Str;

class DummySalesSeeder extends Seeder
{
    public function run()
    {
        $cabangs = Cabang::all();
        $barangFisik = MasterBarang::whereIn('tipe_barang', ['Fisik ACC', 'Fisik Voucher Kartu'])->get();
        $layananDigital = MasterBarang::where('tipe_barang', '!=', 'Fisik ACC')->where('tipe_barang', '!=', 'Fisik Voucher Kartu')->get();
        
        $kasirs = User::where('role', 'kasir')->get();
        if ($kasirs->isEmpty() || $cabangs->isEmpty() || $barangFisik->isEmpty()) {
            $this->command->info('Data master kurang untuk generate dummy sales.');
            return;
        }

        // 1. Generate Dummy Transactions for the last 30 days
        $this->command->info('Generating dummy transactions for the last 30 days...');
        
        for ($i = 30; $i >= 0; $i--) {
            $tgl = Carbon::now()->subDays($i)->startOfDay();
            
            // Randomize how many transactions per day across all cabangs
            foreach ($cabangs as $cabang) {
                $kasirId = $kasirs->where('cabang_id', $cabang->id)->first()->id ?? $kasirs->first()->id;
                
                $numTrx = rand(2, 6);
                for ($j = 0; $j < $numTrx; $j++) {
                    $trxTime = $tgl->copy()->addHours(rand(9, 21))->addMinutes(rand(0, 59));
                    
                    $isFisik = (rand(1, 100) <= 70); // 70% chance of physical item transaction
                    
                    $metode = rand(1, 100) <= 70 ? 'cash' : 'qris';
                    
                    $trx = Transaksi::create([
                        'tanggal_transaksi' => $trxTime,
                        'cabang_id' => $cabang->id,
                        'kasir_id' => $kasirId,
                        'total_tagihan' => 0, // will update later
                        'metode_pembayaran' => $metode,
                        'fee_transaksi' => 0,
                        'created_at' => $trxTime,
                        'updated_at' => $trxTime,
                    ]);

                    $totalTagihan = 0;
                    
                    // Create Detail Transaksi
                    if ($isFisik) {
                        // 1 to 3 items
                        $numItems = rand(1, 3);
                        for ($k = 0; $k < $numItems; $k++) {
                            $barang = $barangFisik->random();
                            $qty = rand(1, 3);
                            $harga = $barang->harga_jual > 0 ? $barang->harga_jual : rand(10000, 50000);
                            $hpp = $barang->harga_modal > 0 ? $barang->harga_modal : $harga * 0.7;
                            
                            DetailTransaksi::create([
                                'transaksi_id' => $trx->id,
                                'barang_id' => $barang->id,
                                'kuantitas' => $qty,
                                'harga_jual_final' => $harga,
                                'hpp_satuan' => $hpp,
                                'jenis_item' => 'Barang',
                            ]);
                            $totalTagihan += ($qty * $harga);
                        }
                    } else {
                        // Digital Transaction
                        $barang = $layananDigital->isEmpty() ? null : $layananDigital->random();
                        $harga = 50000;
                        $hpp = 49000;
                        if ($barang) {
                            $harga = $barang->harga_jual > 0 ? $barang->harga_jual : 50000;
                            $hpp = $barang->harga_modal > 0 ? $barang->harga_modal : 49000;
                        }
                        
                        DetailTransaksi::create([
                            'transaksi_id' => $trx->id,
                            'barang_id' => $barang ? $barang->id : null,
                            'kuantitas' => 1,
                            'harga_jual_final' => $harga,
                            'hpp_satuan' => $hpp,
                            'jenis_item' => 'Layanan',
                        ]);
                        $totalTagihan += $harga;
                        $trx->fee_transaksi = 2000;
                    }
                    
                    $trx->total_tagihan = $totalTagihan;
                    $trx->save();
                }
            }
        }

        // 2. Generate Historical Aggregation Data for the last 6 months
        $this->command->info('Generating historical data for SES for the last 6 months...');
        
        for ($m = 6; $m >= 1; $m--) {
            $period = Carbon::now()->subMonths($m)->format('Y-m');
            
            foreach ($cabangs as $cabang) {
                foreach ($barangFisik as $barang) {
                    // Check if exists
                    $exists = DataHistorisAgregasi::where('cabang_id', $cabang->id)
                                ->where('barang_id', $barang->id)
                                ->where('periode_bulan', $period)
                                ->exists();
                                
                    if (!$exists) {
                        // Create realistic random sales data
                        DataHistorisAgregasi::create([
                            'cabang_id' => $cabang->id,
                            'barang_id' => $barang->id,
                            'periode_bulan' => $period,
                            'total_unit_terjual' => rand(15, 80),
                        ]);
                    }
                }
            }
        }

        $this->command->info('Dummy data generation completed!');
    }
}
