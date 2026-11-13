<?php

namespace App\Services;

use App\Models\DataHistorisAgregasi;
use App\Models\MasterBarang;
use App\Models\BarangMasuk;
use App\Models\Cabang;
use App\Models\HasilPrediksiSes;
use Illuminate\Support\Carbon;

class SesForecastingService
{
    /**
     * Hitung peramalan SES dengan Auto-Alpha (mencari MAPE terkecil).
     *
     * @param int $cabangId ID Cabang
     * @param int $barangId ID Barang Fisik
     * @param string $periodeForecast Pilihan: 'hari', 'minggu', 'bulan'
     * @return array
     */
    public function calculateAutoAlphaForecast(int $cabangId, int $barangId, string $periodeForecast = 'bulan'): array
    {
        if ($cabangId == 0) {
            // Gudang Pusat (Aggregate all branches)
            $dataHistoris = DataHistorisAgregasi::select('periode_bulan')
                ->selectRaw('SUM(total_unit_terjual) as total_unit_terjual')
                ->where('barang_id', $barangId)
                ->groupBy('periode_bulan')
                ->orderBy('periode_bulan', 'desc')
                ->limit(6)
                ->get()
                ->reverse()
                ->values();
        } else {
            // 1. Ambil data agregasi 6 bulan kebelakang
            $dataHistoris = DataHistorisAgregasi::where('cabang_id', $cabangId)
                ->where('barang_id', $barangId)
                ->orderBy('periode_bulan', 'desc')
                ->limit(6)
                ->get()
                ->reverse()
                ->values(); // Reindex array agar mulai dari 0
        }

        if ($dataHistoris->count() < 2) {
            return [
                'success' => false,
                'message' => 'Data historis tidak cukup (minimal butuh 2 bulan data) untuk melakukan peramalan.',
            ];
        }

        $alphas = [0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9];
        $bestAlpha = 0.1;
        $lowestMad = INF;
        $bestMape = 0;
        $bestForecastNextMonth = 0;
        $historyMape = [];

        // 3. Looping perhitungan SES untuk mencari Alpha terbaik
        foreach ($alphas as $alpha) {
            $mape = 0;
            $mad = 0;
            $totalAbsoluteError = 0;
            $totalPercentageError = 0;
            $countValidError = 0;
            $countNonZeroAktual = 0;
            $forecastSebelumnya = null;

            foreach ($dataHistoris as $index => $data) {
                $aktual = $data->total_unit_terjual;

                if ($index === 0) {
                    // Inisialisasi: F1 = X1
                    $forecast = $aktual;
                } else {
                    // Hitung forecast untuk periode ini (Ft)
                    $forecast = ($alpha * $dataHistoris[$index - 1]->total_unit_terjual) + ((1 - $alpha) * $forecastSebelumnya);
                    
                    // Hitung Error
                    $absoluteError = abs($aktual - $forecast);
                    $totalAbsoluteError += $absoluteError;
                    
                    if ($aktual > 0) {
                        $percentageError = ($absoluteError / $aktual) * 100;
                        $totalPercentageError += $percentageError;
                        $countNonZeroAktual++;
                    }
                    $countValidError++;
                }
                
                $forecastSebelumnya = $forecast;
            }

            // Hitung rata-rata MAPE (periode non-zero) & MAD (seluruh periode) untuk alpha ini
            $mape = $countNonZeroAktual > 0 ? ($totalPercentageError / $countNonZeroAktual) : 0;
            $mad = $countValidError > 0 ? ($totalAbsoluteError / $countValidError) : 0;
            
            // Simpan history untuk ditampilkan ke layar statistik
            $historyMape[] = [
                'alpha' => $alpha,
                'mape' => round($mape, 2),
                'mad' => round($mad, 2)
            ];

            // 5. Pilih nilai Alpha yang menghasilkan MAD terkecil (lebih stabil daripada MAPE)
            if ($mad < $lowestMad) {
                $lowestMad = $mad;
                $bestMape = $mape;
                $bestAlpha = $alpha;
                
                // Hitung forecast untuk bulan DEPAN menggunakan alpha terbaik ini
                // F(t+1) = alpha * X(t) + (1 - alpha) * F(t)
                $aktualTerakhir = $dataHistoris->last()->total_unit_terjual;
                $bestForecastNextMonth = ($bestAlpha * $aktualTerakhir) + ((1 - $bestAlpha) * $forecastSebelumnya);
            }
        }

        // 2. Sesuaikan nilai peramalan dengan periode yang dipilih Owner
        // Karena basis data kita adalah bulanan, maka:
        $nilaiPeramalan = $bestForecastNextMonth;
        if ($periodeForecast === 'minggu') {
            $nilaiPeramalan = $bestForecastNextMonth / 4; // Asumsi 1 bulan = 4 minggu
        } elseif ($periodeForecast === 'hari') {
            $nilaiPeramalan = $bestForecastNextMonth / 30; // Asumsi 1 bulan = 30 hari
        }

        $prediksiLaba = 0;
        $barang = MasterBarang::find($barangId);
        if ($barang) {
            // Cari HPP dari BarangMasuk terbaru (FIFO)
            $pembelianTerakhir = BarangMasuk::where('barang_fisik_id', $barangId)->latest()->first();
            $hpp = 0;
            if ($pembelianTerakhir && $pembelianTerakhir->qty_beli > 0) {
                $hpp = $pembelianTerakhir->total_harga_beli / $pembelianTerakhir->qty_beli;
            }
            $labaPerUnit = $barang->harga_jual - $hpp;
            $prediksiLaba = ceil($nilaiPeramalan) * $labaPerUnit;
        }

        // 6. Return array hasil
        return [
            'success' => true,
            'periode_dipilih' => $periodeForecast,
            'nilai_peramalan' => ceil($nilaiPeramalan), // Dibulatkan ke atas untuk stok barang fisik
            'prediksi_laba' => max(0, $prediksiLaba), // Jangan biarkan laba negatif untuk grafik Owner
            'best_alpha' => $bestAlpha,
            'terkecil_mape' => round($bestMape, 2),
            'terkecil_mad' => round($lowestMad, 2),
            'history_mape' => $historyMape,
            'data_aktual_6_bulan' => $dataHistoris->pluck('total_unit_terjual', 'periode_bulan')->toArray()
        ];
    }
    
    public function calculateDynamicForecast(int $cabangId, int $barangId, int $rangeDays): array
    {
        // 1. Tarik Data Historis 6 bulan kebelakang (DetailTransaksi join Transaksi)
        $sixMonthsAgo = Carbon::now()->subMonths(6)->startOfDay();
        
        $query = \App\Models\DetailTransaksi::select('detail_transaksi.kuantitas', 'transaksi.tanggal_transaksi')
            ->join('transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')
            ->where('detail_transaksi.barang_id', $barangId)
            ->where('transaksi.tanggal_transaksi', '>=', $sixMonthsAgo);

        if ($cabangId != 0) {
            $query->where('transaksi.cabang_id', $cabangId);
        }

        $rawTransactions = $query->orderBy('transaksi.tanggal_transaksi', 'asc')->get();
            
        // 2. Agregasi per range_days
        $groupedData = [];
        $anchorDate = $sixMonthsAgo->copy();
        
        foreach ($rawTransactions as $trx) {
            $trxDate = Carbon::parse($trx->tanggal_transaksi);
            $diffDays = abs($trxDate->diffInDays($anchorDate)); // Gunakan abs() agar nilainya selalu positif
            $periodIndex = floor($diffDays / $rangeDays);
            
            if (!isset($groupedData[$periodIndex])) {
                $groupedData[$periodIndex] = 0;
            }
            $groupedData[$periodIndex] += $trx->kuantitas;
        }
        
        // Pastikan array period index terisi walau 0
        $maxPeriod = count($groupedData) > 0 ? max(array_keys($groupedData)) : 0;
        $aggregatedValues = [];
        $chartLabels = [];
        
        // Menentukan label bulan agar terlihat rapi
        for ($i = 0; $i <= $maxPeriod; $i++) {
            $val = isset($groupedData[$i]) ? $groupedData[$i] : 0;
            $aggregatedValues[] = $val;
            
            $startPeriodDate = $anchorDate->copy()->addDays($i * $rangeDays);
            $endPeriodDate = $startPeriodDate->copy()->addDays($rangeDays - 1);
            $chartLabels[] = $startPeriodDate->format('d M') . ' - ' . $endPeriodDate->format('d M');
        }

        // Validasi minimal rentang waktu data historis (6 bulan = ~150 hari agar tidak terlalu kaku)
        $totalDaysAvailable = $maxPeriod * $rangeDays;
        
        if ($totalDaysAvailable < 150) {
            return [
                'success' => false,
                'message' => 'Data historis tidak cukup. Peramalan AI membutuhkan riwayat penjualan barang ini minimal selama 6 bulan terakhir (saat ini hanya terekam sekitar ' . $totalDaysAvailable . ' hari).',
            ];
        }

        if (count($aggregatedValues) < 2) {
            return [
                'success' => false,
                'message' => 'Data historis tidak cukup (butuh minimal 2 periode agregasi).',
            ];
        }

        // 3. Algoritma SES
        $alphas = [0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9];
        $bestAlpha = 0.1;
        $lowestMad = INF;
        $bestMape = 0;
        $bestForecastNext = 0;
        $historyMape = [];
        $bestForecastCurve = [];

        foreach ($alphas as $alpha) {
            $mape = 0;
            $mad = 0;
            $totalAbsoluteError = 0;
            $totalPercentageError = 0;
            $countValidError = 0;
            $countNonZeroAktual = 0;
            $forecastSebelumnya = null;
            $currentForecastCurve = [];

            foreach ($aggregatedValues as $index => $aktual) {
                if ($index === 0) {
                    $forecast = $aktual;
                } else {
                    $forecast = ($alpha * $aggregatedValues[$index - 1]) + ((1 - $alpha) * $forecastSebelumnya);
                    
                    $absoluteError = abs($aktual - $forecast);
                    $totalAbsoluteError += $absoluteError;
                    
                    if ($aktual > 0) {
                        $percentageError = ($absoluteError / $aktual) * 100;
                        $totalPercentageError += $percentageError;
                        $countNonZeroAktual++;
                    }
                    $countValidError++;
                }
                
                $currentForecastCurve[] = round($forecast, 2);
                $forecastSebelumnya = $forecast;
            }

            // Hitung rata-rata MAPE (periode non-zero) & MAD (seluruh periode) untuk alpha ini
            $mape = $countNonZeroAktual > 0 ? ($totalPercentageError / $countNonZeroAktual) : 0;
            $mad = $countValidError > 0 ? ($totalAbsoluteError / $countValidError) : 0;
            $mapeRound = round($mape, 2);
            $madRound = round($mad, 2);
            
            $aktualTerakhir = end($aggregatedValues);
            $forecastNext = ($alpha * $aktualTerakhir) + ((1 - $alpha) * $forecastSebelumnya);
            
            $historyMape[] = [
                'alpha' => $alpha,
                'mape' => $mapeRound,
                'mad' => $madRound,
                'forecast_next' => ceil($forecastNext)
            ];

            if ($mad < $lowestMad) {
                $lowestMad = $mad;
                $bestMape = $mape;
                $bestAlpha = $alpha;
                $bestForecastNext = $forecastNext;
                $bestForecastCurve = $currentForecastCurve;
            }
        }

        // Tambahkan proyeksi ke masa depan untuk ditampilkan di grafik
        $futurePeriods = 4; // Menampilkan 4 blok periode ke depan
        
        $chartForecastFitted = $bestForecastCurve;
        $chartForecastFuture = array_fill(0, count($bestForecastCurve), null); // Pad dengan null
        
        // Sambungkan titik terakhir fitting ke future agar garisnya nyambung di grafik
        if (count($bestForecastCurve) > 0) {
            $chartForecastFuture[count($bestForecastCurve) - 1] = end($bestForecastCurve);
        }

        for ($i = 1; $i <= $futurePeriods; $i++) {
            $futureDateStart = $anchorDate->copy()->addDays(($maxPeriod + $i) * $rangeDays);
            $futureDateEnd = $futureDateStart->copy()->addDays($rangeDays - 1);
            
            $chartLabels[] = $futureDateStart->format('d M') . ' (Future)';
            $aggregatedValues[] = null; // Tidak ada data aktual di masa depan
            $chartForecastFitted[] = null;
            $chartForecastFuture[] = round($bestForecastNext, 2); // Nilai ramalan konstan (SES)
        }

        return [
            'success' => true,
            'best_alpha' => $bestAlpha,
            'terkecil_mape' => round($bestMape, 2),
            'terkecil_mad' => round($lowestMad, 2),
            'nilai_peramalan' => ceil($bestForecastNext),
            'history_mape' => $historyMape,
            'chart_labels' => $chartLabels,
            'chart_aktual' => $aggregatedValues,
            'chart_forecast_fitted' => $chartForecastFitted,
            'chart_forecast_future' => $chartForecastFuture,
        ];
    }


    /**
     * Hitung peramalan untuk semua cabang & barang fisik, simpan ke database.
     */
    public function calculateForecasting()
    {
        $cabangs = Cabang::all();
        $barangFisiks = MasterBarang::whereIn('tipe_barang', ['Fisik ACC', 'Fisik Voucher Kartu'])->get();
        $periodeDepan = Carbon::now()->format('Y-m');

        foreach ($cabangs as $cabang) {
            foreach ($barangFisiks as $barang) {
                $hasil = $this->calculateAutoAlphaForecast($cabang->id, $barang->id, 'bulan');
                
                if ($hasil['success']) {
                    HasilPrediksiSes::updateOrCreate(
                        [
                            'cabang_id' => $cabang->id,
                            'barang_id' => $barang->id,
                            'periode_prediksi' => $periodeDepan,
                        ],
                        [
                            'angka_rekomendasi' => $hasil['nilai_peramalan'],
                            'prediksi_laba' => $hasil['prediksi_laba'],
                        ]
                    );
                }
            }
        }
    }
}
