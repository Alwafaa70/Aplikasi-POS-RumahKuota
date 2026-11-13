<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\MutasiStok;
use App\Models\Cabang;
use App\Models\LaporanClosing;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Tampilkan Halaman Reports (Owner Only).
     */
    public function index(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());
        $cabangId  = $request->query('cabang_id', '');
        
        $tab = $request->query('tab', 'profitability');

        // 1. Laporan Profitabilitas & Chart Data
        $profitability = $this->getProfitability($startDate, $endDate, $cabangId);

        // 2. Laporan Distribusi Aset
        $distribusi = $this->getDistribusi($startDate, $endDate, $cabangId);

        // 3. Laporan Performa Cabang
        $performaCabang = $this->getPerformaCabang($startDate, $endDate, $cabangId);

        // 4. Monitoring Closing
        $monitoringClosing = $this->getMonitoringClosing($startDate, $endDate, $cabangId);

        // 5. Rincian Transaksi Mentah
        $rincianTransaksi = $this->getRincianTransaksi($startDate, $endDate, $cabangId);
        
        $semuaCabang = Cabang::orderBy('nama_cabang')->get();

        return view('owner.reports.index', compact(
            'startDate', 'endDate', 'cabangId', 'tab', 'semuaCabang',
            'profitability', 'distribusi', 'performaCabang', 'monitoringClosing', 'rincianTransaksi'
        ));
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());
        $cabangId  = $request->query('cabang_id', '');

        $profitability = $this->getProfitability($startDate, $endDate, $cabangId);
        $distribusi = $this->getDistribusi($startDate, $endDate, $cabangId);
        $monitoringClosing = $this->getMonitoringClosing($startDate, $endDate, $cabangId);
        $rincianTransaksi = $this->getRincianTransaksi($startDate, $endDate, $cabangId);
        
        $cabangFilter = $cabangId ? Cabang::find($cabangId)->nama_cabang : 'Semua Cabang';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('owner.reports.export_pdf', compact(
            'startDate', 'endDate', 'cabangFilter', 'profitability', 'distribusi', 'monitoringClosing', 'rincianTransaksi'
        ));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Eksekutif_' . $startDate . '_sd_' . $endDate . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());
        $cabangId  = $request->query('cabang_id', '');

        $profitability = $this->getProfitability($startDate, $endDate, $cabangId);
        $distribusi = $this->getDistribusi($startDate, $endDate, $cabangId);
        $monitoringClosing = $this->getMonitoringClosing($startDate, $endDate, $cabangId);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\OwnerReportExport($profitability, $distribusi, $monitoringClosing), 
            'Laporan_Eksekutif_' . $startDate . '_sd_' . $endDate . '.xlsx'
        );
    }

    private function getProfitability($startDate, $endDate, $cabangId)
    {
        $query = Transaksi::whereBetween('tanggal_transaksi', [$startDate, $endDate]);
        if ($cabangId) {
            $query->where('cabang_id', $cabangId);
        }
        $transaksi = $query->get();
        
        $totalPendapatan = 0;
        $totalHpp = 0;
        $totalFeeDigitalB = 0;
        
        $countCash = 0;
        $countQris = 0;
        $countDigitalB = 0;
        
        $trendHarian = []; // Untuk Chart.js

        foreach ($transaksi as $trx) {
            $totalPendapatan += $trx->total_tagihan;
            $totalFeeDigitalB += $trx->fee_transaksi;
            
            // Hitung komposisi pembayaran
            if ($trx->metode_pembayaran == 'cash') {
                $countCash++;
            } elseif ($trx->metode_pembayaran == 'qris') {
                $countQris++;
            } else {
                $countDigitalB++; // transfer/tarik_tunai (Digital B)
            }
            
            // Siapkan data tren harian
            $tgl = Carbon::parse($trx->tanggal_transaksi)->format('Y-m-d');
            if (!isset($trendHarian[$tgl])) {
                $trendHarian[$tgl] = 0;
            }
            $trendHarian[$tgl] += $trx->total_tagihan;
        }
        
        // Urutkan tren berdasarkan tanggal
        ksort($trendHarian);
        $chartLabels = [];
        $chartData = [];
        foreach ($trendHarian as $tgl => $omzet) {
            $chartLabels[] = Carbon::parse($tgl)->format('d M');
            $chartData[] = $omzet;
        }

        $queryDetail = DetailTransaksi::whereHas('transaksi', function($q) use ($startDate, $endDate, $cabangId) {
            $q->whereBetween('tanggal_transaksi', [$startDate, $endDate]);
            if ($cabangId) {
                $q->where('cabang_id', $cabangId);
            }
        });
        $detailTransaksi = $queryDetail->get();

        foreach ($detailTransaksi as $dt) {
            $totalHpp += ($dt->hpp_satuan * $dt->kuantitas);
        }

        $labaKotor = $totalPendapatan - $totalHpp + $totalFeeDigitalB;

        return [
            'total_pendapatan'    => $totalPendapatan,
            'total_hpp'           => $totalHpp,
            'total_fee_digital_b' => $totalFeeDigitalB,
            'laba_kotor'          => $labaKotor,
            
            // Untuk Chart
            'chart_labels'        => $chartLabels,
            'chart_data'          => $chartData,
            'metode_cash'         => $countCash,
            'metode_qris'         => $countQris,
            'metode_digital'      => $countDigitalB,
        ];
    }

    private function getDistribusi($startDate, $endDate, $cabangId)
    {
        $query = MutasiStok::with(['cabangTujuan', 'details.barang'])
            ->whereNull('cabang_asal_id')
            ->whereBetween('tanggal_mutasi', [$startDate, $endDate]);
            
        if ($cabangId) {
            $query->where('cabang_tujuan_id', $cabangId);
        }

        return $query->orderBy('tanggal_mutasi', 'desc')
            ->get()
            ->map(function ($mutasi) {
                $nilaiModal = 0;
                foreach ($mutasi->details as $detail) {
                    $nilaiModal += ($detail->jumlah * $detail->barang->harga_modal);
                }
                $mutasi->nilai_modal_dipindahkan = $nilaiModal;
                return $mutasi;
            });
    }

    private function getPerformaCabang($startDate, $endDate, $cabangId)
    {
        $query = Cabang::withCount(['transaksi as total_transaksi' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal_transaksi', [$startDate, $endDate]);
            }])
            ->withSum(['transaksi as total_pendapatan' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal_transaksi', [$startDate, $endDate]);
            }], 'total_tagihan')
            ->withSum(['transaksi as total_fee' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal_transaksi', [$startDate, $endDate]);
            }], 'fee_transaksi');
            
        if ($cabangId) {
            $query->where('id', $cabangId);
        }

        return $query->get()->sortByDesc('total_pendapatan'); // Ranking otomatis
    }

    private function getMonitoringClosing($startDate, $endDate, $cabangId)
    {
        $query = LaporanClosing::with('kasir', 'cabang', 'details.masterAplikasiLayanan')
            ->whereBetween('tanggal', [$startDate, $endDate]);
            
        if ($cabangId) {
            $query->where('cabang_id', $cabangId);
        }

        return $query->orderBy('tanggal', 'desc')->get();
    }
    
    private function getRincianTransaksi($startDate, $endDate, $cabangId)
    {
        $query = Transaksi::with(['kasir', 'cabang', 'detailTransaksi.barang'])
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate]);
            
        if ($cabangId) {
            $query->where('cabang_id', $cabangId);
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Endpoint API JSON Khusus untuk Grafik Proyeksi Laba Owner (Tahap 16)
     */
    public function getChartData(Request $request)
    {
        // Tetapkan filter 1 bulan terakhir secara default untuk dashboard owner
        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate   = Carbon::now()->endOfDay();

        $transaksis = Transaksi::with('detailTransaksi.barang')
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
            ->orderBy('tanggal_transaksi', 'asc')
            ->get();

        // 1. DATA AREA CHART (Omzet vs Laba Bersih)
        $omzetHarian = [];
        $labaHarian = [];
        
        // 3. DATA METODE PEMBAYARAN
        $countCash = 0;
        $countQris = 0;
        $countDigitalB = 0;
        
        // 4. DATA PROPORSIONAL FISIK VS DIGITAL
        $totalFisik = 0;
        $totalLayananDigital = 0;
        
        foreach ($transaksis as $trx) {
            $tgl = Carbon::parse($trx->tanggal_transaksi)->format('d M');
            if (!isset($omzetHarian[$tgl])) {
                $omzetHarian[$tgl] = 0;
                $labaHarian[$tgl] = 0;
            }
            
            $omzetHarian[$tgl] += $trx->total_tagihan;
            $hppTrx = 0;
            foreach ($trx->detailTransaksi as $dt) {
                $hppTrx += ($dt->hpp_satuan * $dt->kuantitas);
                
                // Pisahkan omzet ke fisik atau digital
                if ($dt->barang && in_array($dt->barang->tipe_barang, ['Fisik ACC', 'Fisik Voucher Kartu'])) {
                    $totalFisik += ($dt->harga_jual_final * $dt->kuantitas);
                } else {
                    $totalLayananDigital += ($dt->harga_jual_final * $dt->kuantitas);
                }
            }
            // Tambahkan fee ke layanan digital
            $totalLayananDigital += $trx->fee_transaksi;

            // Laba = Total Tagihan - HPP + Fee Digital
            $labaHarian[$tgl] += ($trx->total_tagihan - $hppTrx + $trx->fee_transaksi);

            // Hitung komposisi pembayaran
            if ($trx->metode_pembayaran == 'cash') {
                $countCash++;
            } elseif ($trx->metode_pembayaran == 'qris') {
                $countQris++;
            } else {
                $countDigitalB++; 
            }
        }

        // 2. DATA BAR CHART (Top 5 Cabang Terlaris)
        $topCabang = Cabang::withSum(['transaksi as total_pendapatan' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal_transaksi', [$startDate, $endDate]);
            }], 'total_tagihan')
            ->get()
            ->sortByDesc('total_pendapatan')
            ->take(5);

        return response()->json([
            'area_chart' => [
                'categories' => array_keys($omzetHarian),
                'series' => [
                    ['name' => 'Omzet Kotor', 'data' => array_values($omzetHarian)],
                    ['name' => 'Laba Bersih', 'data' => array_values($labaHarian)],
                ]
            ],
            'bar_chart' => [
                'categories' => $topCabang->pluck('nama_cabang')->toArray(),
                'series' => [
                    ['name' => 'Pendapatan', 'data' => $topCabang->pluck('total_pendapatan')->map(fn($v) => $v ?? 0)->toArray()]
                ]
            ],
            'metode_chart' => [
                'labels' => ['Tunai (Cash)', 'QRIS'],
                'series' => [(int)$countCash, (int)$countQris]
            ],
            'kategori_chart' => [
                'labels' => ['Produk Fisik', 'Layanan Digital (PPOB & Transfer)'],
                'series' => [(float)$totalFisik, (float)$totalLayananDigital]
            ]
        ]);
    }
}
