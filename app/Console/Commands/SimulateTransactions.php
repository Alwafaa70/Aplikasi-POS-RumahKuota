<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MasterBarang;
use App\Models\Cabang;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SimulateTransactions extends Command
{
    protected $signature = 'simulate:transactions {days=180}';
    protected $description = 'Simulate random transactions for physical items over the past N days to populate EWS trends';

    public function handle()
    {
        $days = (int) $this->argument('days');
        $this->info("Simulating transactions for the last $days days...");

        $cabangs = Cabang::all();
        $barangs = MasterBarang::where('tipe_barang', 'like', '%Fisik%')->get();
        
        if ($cabangs->isEmpty() || $barangs->isEmpty()) {
            $this->error('No branches or physical items found.');
            return;
        }

        // Generate a random daily base trend (popularity) for each item per branch
        // e.g. some items sell 10 a day, some sell 0.5 a day
        $itemTrends = [];
        foreach ($cabangs as $cabang) {
            $itemTrends[$cabang->id] = [];
            foreach ($barangs as $barang) {
                // random trend between 1.0 to 15.0
                $itemTrends[$cabang->id][$barang->id] = rand(10, 150) / 10; 
            }
        }

        $kasirPerCabang = [];
        foreach ($cabangs as $cabang) {
            $kasir = User::where('role', 'kasir')->where('cabang_id', $cabang->id)->first();
            if (!$kasir) {
                // fallback to any user if no kasir found
                $kasir = User::where('cabang_id', $cabang->id)->first() ?? User::first();
            }
            $kasirPerCabang[$cabang->id] = $kasir->id;
        }

        $now = Carbon::now();
        $startDate = $now->copy()->subDays($days);
        
        DB::beginTransaction();
        try {
            $progress = $this->output->createProgressBar($days + 1);
            $progress->start();

            for ($d = 0; $d <= $days; $d++) {
                $currentDate = $startDate->copy()->addDays($d);
                
                foreach ($cabangs as $cabang) {
                    $kasirId = $kasirPerCabang[$cabang->id];
                    
                    // We generate a few transaction receipts per day for this branch
                    $numTransactions = rand(2, 6);
                    
                    for ($t = 0; $t < $numTransactions; $t++) {
                        // determine items for this transaction
                        $cart = [];
                        $totalTagihan = 0;
                        
                        foreach ($barangs as $barang) {
                            $trend = $itemTrends[$cabang->id][$barang->id];
                            
                            // chance to be bought in this transaction, adjusted by trend
                            if (rand(1, 100) <= ($trend * 5)) { 
                                $qty = rand(1, ceil($trend / 2) > 0 ? ceil($trend / 2) : 1);
                                $harga = $barang->harga_jual;
                                $cart[] = [
                                    'barang_id' => $barang->id,
                                    'qty' => $qty,
                                    'harga' => $harga,
                                    'jenis_item' => 'fisik',
                                    // get dummy HPP
                                    'hpp_satuan' => 0 
                                ];
                                $totalTagihan += ($qty * $harga);
                            }
                        }
                        
                        if (empty($cart)) continue; // skip if nothing bought
                        
                        // transaction time: random between 08:00 and 20:00
                        $txTime = $currentDate->copy()->addHours(rand(8, 20))->addMinutes(rand(0, 59));
                        
                        $transaksi = Transaksi::create([
                            'kasir_id' => $kasirId,
                            'cabang_id' => $cabang->id,
                            'total_tagihan' => $totalTagihan,
                            'uang_diterima' => $totalTagihan,
                            'kembalian' => 0,
                            'metode_pembayaran' => 'cash',
                            'tanggal_transaksi' => $txTime,
                            'created_at' => $txTime,
                            'updated_at' => $txTime,
                            'fee_transaksi' => 0,
                            'total_diskon' => 0,
                            'is_bantuan_pusat' => 0
                        ]);
                        
                        foreach ($cart as $item) {
                            DetailTransaksi::create([
                                'transaksi_id' => $transaksi->id,
                                'barang_id' => $item['barang_id'],
                                'harga_jual_final' => $item['harga'],
                                'kuantitas' => $item['qty'],
                                'created_at' => $txTime,
                                'updated_at' => $txTime,
                                'hpp_satuan' => $item['hpp_satuan'],
                                'jenis_item' => $item['jenis_item'],
                                'diskon' => 0
                            ]);
                        }
                    }
                }
                $progress->advance();
            }
            $progress->finish();
            DB::commit();
            $this->info("\nSuccessfully simulated transactions.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("\nError: " . $e->getMessage());
        }
    }
}
