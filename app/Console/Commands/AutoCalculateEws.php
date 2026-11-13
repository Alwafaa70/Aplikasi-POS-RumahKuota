<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MasterBarang;
use App\Models\Cabang;
use App\Models\PengaturanEws;
use App\Services\SesForecastingService;

class AutoCalculateEws extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ews:auto-calculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghitung nilai EWS secara otomatis berdasarkan tren penjualan (Dynamic Buffer)';

    /**
     * Execute the console command.
     */
    public function handle(SesForecastingService $sesService)
    {
        $this->info('Starting Auto EWS Calculation...');
        $barangs = MasterBarang::whereIn('tipe_barang', ['Fisik ACC', 'Fisik Voucher Kartu'])->get();
        $cabangs = Cabang::all();

        $defaultLeadTime = 7;
        
        $locations = [0]; // 0 = Gudang Pusat
        foreach ($cabangs as $c) {
            $locations[] = $c->id;
        }

        foreach ($barangs as $barang) {
            foreach ($locations as $cabangId) {
                // Hitung tren penjualan dengan memprediksi 30 hari ke depan
                $prediksi = $sesService->calculateDynamicForecast($cabangId, $barang->id, 30);
                
                $dailyForecast = 0;
                if ($prediksi['success'] && $prediksi['nilai_peramalan'] > 0) {
                    $dailyForecast = round($prediksi['nilai_peramalan'] / 30, 2);
                } else {
                    // Fallback: hitung dari riwayat transaksi riil jika ada
                    $query = \App\Models\DetailTransaksi::join('transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')
                        ->where('detail_transaksi.barang_id', $barang->id);
                    if ($cabangId != 0) {
                        $query->where('transaksi.cabang_id', $cabangId);
                    }
                    $totalSold = (float) $query->sum('detail_transaksi.kuantitas');
                    $earliestDate = (clone $query)->min('transaksi.tanggal_transaksi');
                    if ($earliestDate && $totalSold > 0) {
                        $diffDays = max(1, abs(\Illuminate\Support\Carbon::parse($earliestDate)->diffInDays(\Illuminate\Support\Carbon::now())));
                        $dailyForecast = max(0.1, round($totalSold / $diffDays, 2));
                    } else {
                        $dailyForecast = 0.5;
                    }
                }

                // Batas EWS (Buffer) = Rata-rata per hari * Lead Time (minimal 1)
                $batasEws = max(1, (int) ceil($dailyForecast * $defaultLeadTime));

                // Dynamic Safety Stock Percentage berdasarkan Kecepatan Perputaran Barang (FSN / Fast-Medium-Slow Moving Analysis):
                // - Fast-Moving: 30% (Cegah stockout mendadak pada produk primadona penyumbang omset utama)
                // - Medium-Moving: 20% (Permintaan stabil normal)
                // - Slow-Moving: 10% (Produk kurang laku, buffer kecil untuk cegah overstock, dead capital, dan kadaluarsa)
                if ($cabangId == 0) {
                    // Gudang Pusat (Skala gabungan seluruh cabang)
                    if ($dailyForecast >= 20.0) {
                        $safetyStockPercent = 30;
                    } elseif ($dailyForecast >= 10.0) {
                        $safetyStockPercent = 20;
                    } else {
                        $safetyStockPercent = 10;
                    }
                } else {
                    // Level Cabang
                    if ($dailyForecast >= 2.5) {
                        $safetyStockPercent = 30;
                    } elseif ($dailyForecast >= 1.0) {
                        $safetyStockPercent = 20;
                    } else {
                        $safetyStockPercent = 10;
                    }
                }

                PengaturanEws::updateOrCreate(
                    [
                        'barang_id' => $barang->id,
                        'cabang_id' => $cabangId == 0 ? null : $cabangId
                    ],
                    [
                        'daily_forecast' => $dailyForecast,
                        'batas_ews' => $batasEws,
                        'lead_time' => $defaultLeadTime,
                        'safety_stock_percent' => $safetyStockPercent,
                        'hari_peramalan' => 30
                    ]
                );
            }
            $this->info("Calculated EWS for {$barang->nama_barang}");
        }

        $this->info('Auto EWS Calculation completed successfully.');
    }
}
