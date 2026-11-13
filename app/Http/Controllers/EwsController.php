<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterBarang;
use App\Models\Cabang;
use App\Models\PengaturanEws;
use App\Models\StokCabang;
use App\Models\BarangMasuk;
use Illuminate\Support\Facades\Artisan;
use Barryvdh\DomPDF\Facade\Pdf;

class EwsController extends Controller
{
    /**
     * Helper untuk mengumpulkan kalkulasi Buffer EWS seluruh barang fisik.
     */
    private function collectEwsData($targetHari = 30)
    {
        $barangs = MasterBarang::with('category')->whereIn('tipe_barang', ['Fisik ACC', 'Fisik Voucher Kartu'])->get();
        $cabangs = Cabang::all();

        // 1. Buffer EWS Gudang Pusat
        $ewsGudang = [];
        foreach ($barangs as $barang) {
            $pengaturan = PengaturanEws::where('barang_id', $barang->id)->whereNull('cabang_id')->first();
            $stok = (int) BarangMasuk::where('barang_fisik_id', $barang->id)->sum('stok_sisa');
            
            $dailyForecast = $pengaturan ? (float) $pengaturan->daily_forecast : 0.5;
            $leadTime = $pengaturan ? (int) $pengaturan->lead_time : 7;
            $batasEws = $pengaturan ? (int) $pengaturan->batas_ews : max(1, (int) ceil($dailyForecast * $leadTime));
            $safetyPercent = $pengaturan ? (int) $pengaturan->safety_stock_percent : 20;

            $klasifikasi = $safetyPercent >= 30 ? 'Fast-Moving (Laris)' : ($safetyPercent == 20 ? 'Medium-Moving (Stabil)' : 'Slow-Moving (Lambat)');
            $alasan = $safetyPercent >= 30 
                ? 'Tren penjualan sangat tinggi. Buffer 30% disiapkan untuk mengantisipasi lonjakan pembeli mendadak agar tidak kehilangan omset utama.'
                : ($safetyPercent == 20 
                    ? 'Tren penjualan stabil. Buffer 20% proporsi ideal menahan fluktuasi tanpa menimbun modal.'
                    : 'Tren penjualan lambat/jarang laku. Buffer sengaja ditekan 10% agar modal kas tidak tertimbun (dead capital) & menghindari resiko barang basi/rusak.');

            $demand = $dailyForecast * $targetHari;
            $safetyUnits = (int) ceil($demand * ($safetyPercent / 100));
            $rekomendasi = max(0, (int) ceil(($demand + $safetyUnits) - $stok));

            $ewsGudang[] = [
                'barang_id' => $barang->id,
                'nama_barang' => $barang->nama_barang,
                'kategori' => $barang->category ? $barang->category->nama_kategori : 'Umum',
                'tipe_barang' => $barang->tipe_barang,
                'lokasi_key' => 'gudang_0',
                'lokasi_nama' => 'Gudang Pusat',
                'tipe_lokasi' => 'Gudang Pusat',
                'stok' => $stok,
                'daily_forecast' => $dailyForecast,
                'lead_time' => $leadTime,
                'batas_ews' => $batasEws,
                'safety_percent' => $safetyPercent,
                'safety_units' => $safetyUnits,
                'klasifikasi_tren' => $klasifikasi,
                'alasan_buffer' => $alasan,
                'rekomendasi_restok' => $rekomendasi,
                'is_kritis' => $stok <= $batasEws
            ];
        }

        // 2. Buffer EWS Tiap Cabang
        $ewsCabang = [];
        foreach ($cabangs as $cabang) {
            foreach ($barangs as $barang) {
                $pengaturan = PengaturanEws::where('barang_id', $barang->id)->where('cabang_id', $cabang->id)->first();
                $stokRow = StokCabang::where('barang_id', $barang->id)->where('cabang_id', $cabang->id)->first();
                $stok = $stokRow ? (int) $stokRow->jumlah_stok : 0;
                
                $dailyForecast = $pengaturan ? (float) $pengaturan->daily_forecast : 0.5;
                $leadTime = $pengaturan ? (int) $pengaturan->lead_time : 7;
                $batasEws = $pengaturan ? (int) $pengaturan->batas_ews : max(1, (int) ceil($dailyForecast * $leadTime));
                $safetyPercent = $pengaturan ? (int) $pengaturan->safety_stock_percent : 20;

                $klasifikasi = $safetyPercent >= 30 ? 'Fast-Moving (Laris)' : ($safetyPercent == 20 ? 'Medium-Moving (Stabil)' : 'Slow-Moving (Lambat)');
                $alasan = $safetyPercent >= 30 
                    ? 'Tren penjualan sangat tinggi. Buffer 30% disiapkan untuk mengantisipasi lonjakan pembeli mendadak agar tidak kehilangan omset utama.'
                    : ($safetyPercent == 20 
                        ? 'Tren penjualan stabil. Buffer 20% proporsi ideal menahan fluktuasi tanpa menimbun modal.'
                        : 'Tren penjualan lambat/jarang laku. Buffer sengaja ditekan 10% agar modal kas tidak tertimbun (dead capital) & menghindari resiko barang basi/rusak.');

                $demand = $dailyForecast * $targetHari;
                $safetyUnits = (int) ceil($demand * ($safetyPercent / 100));
                $rekomendasi = max(0, (int) ceil(($demand + $safetyUnits) - $stok));

                $ewsCabang[] = [
                    'barang_id' => $barang->id,
                    'nama_barang' => $barang->nama_barang,
                    'kategori' => $barang->category ? $barang->category->nama_kategori : 'Umum',
                    'tipe_barang' => $barang->tipe_barang,
                    'lokasi_key' => 'cabang_' . $cabang->id,
                    'lokasi_nama' => $cabang->nama_cabang,
                    'tipe_lokasi' => 'Cabang',
                    'stok' => $stok,
                    'daily_forecast' => $dailyForecast,
                    'lead_time' => $leadTime,
                    'batas_ews' => $batasEws,
                    'safety_percent' => $safetyPercent,
                    'safety_units' => $safetyUnits,
                    'klasifikasi_tren' => $klasifikasi,
                    'alasan_buffer' => $alasan,
                    'rekomendasi_restok' => $rekomendasi,
                    'is_kritis' => $stok <= $batasEws
                ];
            }
        }

        return [
            'allEws' => array_merge($ewsGudang, $ewsCabang),
            'cabangs' => $cabangs
        ];
    }

    public function index()
    {
        // Jika belum ada kalkulasi EWS sama sekali, kalkulasikan otomatis sekali di awal
        if (PengaturanEws::whereNotNull('daily_forecast')->count() === 0) {
            Artisan::call('ews:auto-calculate');
        }

        $data = $this->collectEwsData(30);
        $allEws = $data['allEws'];
        $cabangs = $data['cabangs'];

        return view('owner.ews.index', compact('allEws', 'cabangs'));
    }

    public function runAutoCalculation()
    {
        Artisan::call('ews:auto-calculate');
        return redirect()->route('owner.ews.index')->with('success', 'EWS Buffer (Batas & Safety Stock) seluruh produk berhasil dikalkulasi ulang berdasarkan data tren penjualan AI!');
    }

    public function exportPdf(Request $request)
    {
        $targetHari = $request->target_hari ? (int) $request->target_hari : 30;
        $targetHari = min(30, max(1, $targetHari));
        $lokasiFilter = $request->lokasi ?? 'all';
        $statusFilter = $request->status ?? 'all';

        $data = $this->collectEwsData($targetHari);
        $items = collect($data['allEws']);

        if ($lokasiFilter !== 'all') {
            $items = $items->where('lokasi_key', $lokasiFilter);
        }

        if ($statusFilter === 'kritis') {
            $items = $items->where('is_kritis', true);
        } elseif ($statusFilter === 'aman') {
            $items = $items->where('is_kritis', false);
        }

        $kritisCount = $items->where('is_kritis', true)->count();
        $amanCount = $items->where('is_kritis', false)->count();
        $totalRekomendasi = $items->sum('rekomendasi_restok');

        $lokasiLabel = 'Seluruh Lokasi (Gudang & Cabang)';
        if ($lokasiFilter === 'gudang_0') {
            $lokasiLabel = 'Gudang Pusat';
        } elseif (str_starts_with($lokasiFilter, 'cabang_')) {
            $cid = (int) str_replace('cabang_', '', $lokasiFilter);
            $c = Cabang::find($cid);
            $lokasiLabel = $c ? 'Cabang ' . $c->nama_cabang : 'Cabang';
        }

        $pdf = Pdf::loadView('owner.ews.pdf_report', [
            'items' => $items,
            'targetHari' => $targetHari,
            'lokasiLabel' => $lokasiLabel,
            'statusFilter' => $statusFilter,
            'kritisCount' => $kritisCount,
            'amanCount' => $amanCount,
            'totalRekomendasi' => $totalRekomendasi,
            'printedAt' => now()->format('d F Y H:i:s')
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan_Rekap_Buffer_EWS_' . date('YmdHis') . '.pdf');
    }

    public function apiDetailStats(Request $request, \App\Services\EwsService $ewsService)
    {
        $barangId = $request->barang_id;
        $cabangId = ($request->has('cabang_id') && $request->cabang_id !== null && $request->cabang_id !== '' && $request->cabang_id != '0') 
            ? (int) $request->cabang_id 
            : null;
        $targetHari = $request->target_hari ? (int) $request->target_hari : 30;

        $detail = $ewsService->getItemEwsDetail($barangId, $cabangId, $targetHari);
        if (!$detail) {
            return response()->json(['success' => false, 'message' => 'Barang tidak ditemukan'], 404);
        }

        return response()->json(['success' => true, 'data' => $detail]);
    }
}
