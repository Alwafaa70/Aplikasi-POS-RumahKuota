<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataHistorisAgregasi;
use App\Models\HasilPrediksiSes;
use App\Services\SesForecastingService;
use App\Services\EwsService;
use App\Imports\DataHistorisImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Models\Cabang;
use App\Models\MasterBarang;
use App\Models\StokPusat;
use App\Models\KasPusat;
use App\Models\PusatAplikasiLayanan;
use Barryvdh\DomPDF\Facade\Pdf;

class OwnerController extends Controller
{
    public function gudang()
    {
        // 1. Data Gudang Pusat (Per Batch / FIFO)
        $batchPusat = \App\Models\BarangMasuk::with(['barang.category', 'supplier', 'pembelian'])
            ->where('stok_sisa', '>', 0)
            ->orderBy('created_at', 'asc') // Terlama ke terbaru
            ->get();
            
        $totalAsetModalPusat = 0;
        $totalAsetJualPusat = 0;
        $stokPusatGrouped = [];
        
        $totalStokPusatPerBarang = DB::table('barang_masuk')
            ->select('barang_fisik_id', DB::raw('SUM(stok_sisa) as total_stok'))
            ->groupBy('barang_fisik_id')
            ->pluck('total_stok', 'barang_fisik_id');

        foreach ($batchPusat as $batch) {
            if (!$batch->barang) continue;
            
            $hargaModal = $batch->qty_beli > 0 ? ($batch->total_harga_beli / $batch->qty_beli) : 0;
            $hargaJual = $batch->barang->harga_jual;
            $qty = $batch->stok_sisa;
            
            $subtotalModal = $hargaModal * $qty;
            $subtotalJual = $hargaJual * $qty;
            
            $totalAsetModalPusat += $subtotalModal;
            $totalAsetJualPusat += $subtotalJual;

            // EWS Logic for Gudang Pusat
            $is_kritis = false;
            if (in_array($batch->barang->tipe_barang, ['Fisik ACC', 'Fisik Voucher Kartu'])) {
                $totalStok = $totalStokPusatPerBarang[$batch->barang_fisik_id] ?? 0;
                $pengaturan = \App\Models\PengaturanEws::where('barang_id', $batch->barang_fisik_id)
                    ->whereNull('cabang_id')
                    ->first();
                $threshold = $pengaturan ? $pengaturan->batas_ews : 20;
                
                if ($totalStok <= $threshold) {
                    $is_kritis = true;
                }
            }
            
            $bId = $batch->barang_fisik_id;
            if (!isset($stokPusatGrouped[$bId])) {
                $stokPusatGrouped[$bId] = [
                    'barang_id' => $bId,
                    'nama_barang' => $batch->barang->nama_barang,
                    'kategori' => $batch->barang->category ? $batch->barang->category->name : '-',
                    'tipe_barang' => $batch->barang->tipe_barang,
                    'total_stok' => 0,
                    'total_aset_modal' => 0,
                    'total_aset_jual' => 0,
                    'potensi_laba' => 0,
                    'is_kritis' => $is_kritis,
                    'oldest_batch_date' => $batch->created_at->toIso8601String(),
                    'newest_batch_date' => $batch->created_at->toIso8601String(),
                    'batches' => []
                ];
            }

            $stokPusatGrouped[$bId]['total_stok'] += $qty;
            $stokPusatGrouped[$bId]['total_aset_modal'] += $subtotalModal;
            $stokPusatGrouped[$bId]['total_aset_jual'] += $subtotalJual;
            $stokPusatGrouped[$bId]['potensi_laba'] = $stokPusatGrouped[$bId]['total_aset_jual'] - $stokPusatGrouped[$bId]['total_aset_modal'];
            $stokPusatGrouped[$bId]['newest_batch_date'] = $batch->created_at->toIso8601String(); // Because sorted asc initially

            $stokPusatGrouped[$bId]['batches'][] = [
                'kode_batch' => $batch->kode_batch ?: 'BATCH-'.$batch->id,
                'usia_hari' => (int) floor(\Carbon\Carbon::parse($batch->created_at)->diffInDays(\Carbon\Carbon::now())),
                'tanggal_masuk' => \Carbon\Carbon::parse($batch->created_at)->translatedFormat('d M Y'),
                'supplier' => $batch->supplier ? $batch->supplier->nama_supplier : 'Tanpa Supplier',
                'stok' => $qty,
                'harga_modal_raw' => $hargaModal,
                'subtotal_modal_raw' => $subtotalModal,
                'harga_modal' => 'Rp ' . number_format($hargaModal, 0, ',', '.'),
                'subtotal_modal' => 'Rp ' . number_format($subtotalModal, 0, ',', '.')
            ];
        }

        $stokPusatGrouped = array_values($stokPusatGrouped); // Re-index for alpine
        
        // Sort grouped array by is_kritis true first
        usort($stokPusatGrouped, function ($a, $b) {
            if ($a['is_kritis'] === $b['is_kritis']) {
                return strcmp($a['nama_barang'], $b['nama_barang']);
            }
            return $a['is_kritis'] ? -1 : 1;
        });

        // 2. Data Cabang (Rekap per Cabang)
        $cabangs = Cabang::with(['stokCabang.barang', 'aplikasiLayanan.masterAplikasiLayanan'])->get();
        $rekapCabang = [];
        $grandTotalModalCabang = 0;
        $grandTotalJualCabang = 0;
        $grandTotalKasCabang = 0;
        $grandTotalApkCabang = 0;

        foreach ($cabangs as $cabang) {
            $totalBarang = 0;
            $asetModal = 0;
            $asetJual = 0;
            $has_ews = false;
            $ews_count = 0;

            foreach ($cabang->stokCabang as $stok) {
                $barang = $stok->barang;
                if (!$barang) continue;

                $hargaModal = $barang->harga_modal;
                $hargaJual = $barang->harga_jual;
                $qty = $stok->jumlah_stok;

                if (in_array($barang->tipe_barang, ['Fisik ACC', 'Fisik Voucher Kartu'])) {
                    $totalBarang++; // Hitung jumlah jenis barang, bukan total qty
                    $asetModal += $hargaModal * $qty;
                    $asetJual += $hargaJual * $qty;

                    // EWS Logic
                    $threshold = $barang->batas_ews ?? 20;
                    if ($qty <= $threshold) {
                        $has_ews = true;
                        $ews_count++;
                    }
                }
            }

            $grandTotalModalCabang += $asetModal;
            $grandTotalJualCabang += $asetJual;
            
            $saldoKas = $cabang->saldo_cash_sekarang ?? 0;
            $grandTotalKasCabang += $saldoKas;
            
            $saldoApk = $cabang->aplikasiLayanan->sum('saldo_sekarang');
            $grandTotalApkCabang += $saldoApk;

            $rekapCabang[] = [
                'cabang' => $cabang,
                'total_barang' => $totalBarang,
                'aset_modal' => $asetModal,
                'aset_jual' => $asetJual,
                'potensi_laba' => $asetJual - $asetModal,
                'has_ews' => $has_ews,
                'ews_count' => $ews_count,
                'saldo_kas' => $saldoKas,
                'saldo_apk' => $saldoApk,
            ];
        }

        // Sort by EWS (true first), then by name
        usort($rekapCabang, function ($a, $b) {
            if ($a['has_ews'] === $b['has_ews']) {
                return strcmp($a['cabang']->nama_cabang, $b['cabang']->nama_cabang);
            }
            return $a['has_ews'] ? -1 : 1;
        });

        // 3. Aset Keuangan Pusat
        $kasPusatModel = KasPusat::first();
        $kasPusat = $kasPusatModel ? $kasPusatModel->saldo : 0;
        $detailApkPusat = PusatAplikasiLayanan::with('masterAplikasiLayanan')->get();
        $saldoApkPusat = $detailApkPusat->sum('saldo_sekarang');
        
        $totalAsetKeuanganPusat = $kasPusat + $saldoApkPusat;

        return view('owner.gudang.index', compact(
            'stokPusatGrouped', 'totalAsetModalPusat', 'totalAsetJualPusat', 
            'rekapCabang', 'grandTotalModalCabang', 'grandTotalJualCabang',
            'grandTotalKasCabang', 'grandTotalApkCabang',
            'kasPusat', 'saldoApkPusat', 'totalAsetKeuanganPusat', 'detailApkPusat'
        ));
    }



    public function detailCabang($id)
    {
        $stokCabang = \App\Models\StokCabangBatch::with(['barang.category'])
            ->where('cabang_id', $id)
            ->where('stok_sisa_cabang', '>', 0)
            ->get();
            
        $grouped = [];

        foreach ($stokCabang as $stok) {
            $barang = $stok->barang;
            if (!$barang) continue;
            
            $bId = $barang->id;
            $hargaModal = $stok->hpp_batch ?? $barang->harga_modal;
            $qty = $stok->stok_sisa_cabang;
            $tipe_barang = $barang->tipe_barang;
            
            if (!isset($grouped[$bId])) {
                $is_kritis = false;
                if (in_array($tipe_barang, ['Fisik ACC', 'Fisik Voucher Kartu'])) {
                    // Total stok for this branch across all batches is calculated by summing them up
                    $pengaturan = \App\Models\PengaturanEws::where('barang_id', $bId)
                        ->where('cabang_id', $id)
                        ->first();
                    $threshold = $pengaturan ? $pengaturan->batas_ews : 20;

                    $grouped[$bId] = [
                        'id' => $bId,
                        'nama_barang' => $barang->nama_barang,
                        'kategori' => $barang->category ? $barang->category->name : '-',
                        'tipe_barang' => $tipe_barang,
                        'stok' => 0,
                        'total_modal_raw' => 0,
                        'threshold' => $threshold,
                        'is_kritis' => false
                    ];
                } else {
                     $grouped[$bId] = [
                        'id' => $bId,
                        'nama_barang' => $barang->nama_barang,
                        'kategori' => $barang->category ? $barang->category->name : '-',
                        'tipe_barang' => $tipe_barang,
                        'stok' => 0,
                        'total_modal_raw' => 0,
                        'threshold' => 0,
                        'is_kritis' => false
                    ];
                }
            }
            
            $grouped[$bId]['stok'] += $qty;
            $grouped[$bId]['total_modal_raw'] += ($hargaModal * $qty);
        }
        
        $data = array_values($grouped);
        foreach ($data as &$item) {
            if (in_array($item['tipe_barang'], ['Fisik ACC', 'Fisik Voucher Kartu'])) {
                if ($item['stok'] <= max(10, $item['threshold'])) {
                    $item['is_kritis'] = true;
                }
            }
            $item['harga_modal'] = 'Akumulasi';
            $item['subtotal_modal'] = 'Rp ' . number_format($item['total_modal_raw'], 0, ',', '.');
        }
        
        usort($data, function ($a, $b) {
            return $b['is_kritis'] <=> $a['is_kritis'];
        });

        return response()->json($data);
    }
    public function sesDebugger(Request $request, SesForecastingService $sesService, EwsService $ewsService)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $request->validate([
                'cabang_id' => 'required|integer', // 0 for Gudang Pusat
                'barang_id' => 'required|integer',
                'range_days' => 'required|integer|min:1'
            ]);
            
            $hasilSes = $sesService->calculateDynamicForecast(
                $request->cabang_id,
                $request->barang_id,
                $request->range_days
            );
            
            if ($hasilSes['success']) {
                $barang = MasterBarang::find($request->barang_id);
                $cabangId = ($request->cabang_id == 0) ? null : (int) $request->cabang_id;
                $rangeDays = (int) $request->range_days;

                // Ambil parameter buffer EWS otomatis dari EwsService & Pengaturan EWS (FSN Analysis)
                $ewsDetail = $ewsService->getItemEwsDetail($request->barang_id, $cabangId, $rangeDays);

                $safetyStockPercent = $ewsDetail ? $ewsDetail['safety_percent'] : 20;
                $leadTime = $ewsDetail ? $ewsDetail['lead_time'] : 7;
                $jumlahStok = $ewsDetail ? $ewsDetail['stok_saat_ini'] : 0;
                $klasifikasiFsn = $ewsDetail ? $ewsDetail['klasifikasi_nama'] : 'Normal';
                $klasifikasiBadge = $ewsDetail ? $ewsDetail['klasifikasi_badge'] : 'medium';
                $alasanIlmiah = $ewsDetail ? $ewsDetail['alasan_ilmiah'] : '';

                $ramalan = $hasilSes['nilai_peramalan'];
                $dailyForecast = round($ramalan / $rangeDays, 2);

                // Batas Kritis EWS otomatis = Tren Harian * Lead Time (7 Hari)
                $batasEws = max(1, (int) ceil($dailyForecast * $leadTime));

                // Safety Stock otomatis dari persentase Buffer EWS
                $safetyStock = (int) ceil($ramalan * ($safetyStockPercent / 100));

                // Rekomendasi Restok = max(0, (Ramalan + Safety) - Sisa Stok)
                $rekomendasiRestok = max(0, ($ramalan + $safetyStock) - $jumlahStok);
                $isKritis = ($jumlahStok <= $batasEws);

                $hasilSes['jumlah_stok'] = $jumlahStok;
                $hasilSes['safety_stock_percent'] = $safetyStockPercent;
                $hasilSes['safety_stock'] = $safetyStock;
                $hasilSes['rekomendasi_restok'] = $rekomendasiRestok;
                $hasilSes['barang_id'] = $request->barang_id;
                $hasilSes['nama_barang'] = $barang ? $barang->nama_barang : '';
                $hasilSes['cabang_id'] = $request->cabang_id;
                $hasilSes['daily_forecast'] = $dailyForecast;
                $hasilSes['lead_time'] = $leadTime;
                $hasilSes['batas_ews'] = $batasEws;
                $hasilSes['is_kritis'] = $isKritis;
                $hasilSes['klasifikasi_nama'] = $klasifikasiFsn;
                $hasilSes['klasifikasi_badge'] = $klasifikasiBadge;
                $hasilSes['alasan_ilmiah'] = $alasanIlmiah;
                $hasilSes['lokasi_nama'] = $ewsDetail ? $ewsDetail['lokasi_nama'] : ($request->cabang_id == 0 ? 'Gudang Pusat' : 'Cabang');
                $hasilSes['tipe_lokasi'] = $ewsDetail ? $ewsDetail['tipe_lokasi'] : ($request->cabang_id == 0 ? 'Gudang Pusat' : 'Cabang');
            }

            return response()->json($hasilSes);
        }

        $cabangs = Cabang::all();
        // Add pseudo-cabang for Gudang Pusat
        $gudangPusat = new Cabang();
        $gudangPusat->id = 0;
        $gudangPusat->nama_cabang = "Gudang Pusat (Agregat Seluruh Cabang)";
        $cabangs->prepend($gudangPusat);

        $barangs = MasterBarang::whereIn('tipe_barang', ['Fisik ACC', 'Fisik Voucher Kartu'])->get();
        
        return view('owner.ses_debugger', compact('cabangs', 'barangs'));
    }

    public function exportDebuggerPdf(Request $request, SesForecastingService $sesService, EwsService $ewsService)
    {
        $request->validate([
            'cabang_id' => 'required|integer',
            'barang_id' => 'required|integer',
            'range_days' => 'required|integer|min:1'
        ]);

        $hasil = $sesService->calculateDynamicForecast(
            $request->cabang_id,
            $request->barang_id,
            $request->range_days
        );

        $cabangName = $request->cabang_id == 0 ? 'Gudang Pusat (Agregat Seluruh Cabang)' : Cabang::find($request->cabang_id)->nama_cabang;
        $barang = MasterBarang::find($request->barang_id);
        $barangName = $barang ? $barang->nama_barang : 'Barang Fisik';
        $rangeDays = $request->range_days;

        // Ambil otomatis dari Manajemen Buffer EWS (FSN Analysis)
        $cabangId = ($request->cabang_id == 0) ? null : (int) $request->cabang_id;
        $ewsDetail = $ewsService->getItemEwsDetail($request->barang_id, $cabangId, $rangeDays);

        $safetyStockPercent = $ewsDetail ? $ewsDetail['safety_percent'] : 20;
        $leadTime = $ewsDetail ? $ewsDetail['lead_time'] : 7;
        $jumlahStok = $ewsDetail ? $ewsDetail['stok_saat_ini'] : 0;
        $klasifikasiFsn = $ewsDetail ? $ewsDetail['klasifikasi_nama'] : 'Normal';

        $ramalan = $hasil['success'] ? $hasil['nilai_peramalan'] : 0;
        $dailyForecast = round($ramalan / $rangeDays, 2);
        $batasEws = max(1, (int) ceil($dailyForecast * $leadTime));
        $safetyStock = (int) ceil($ramalan * ($safetyStockPercent / 100));
        $rekomendasiRestok = max(0, ($ramalan + $safetyStock) - $jumlahStok);
        $isKritis = ($jumlahStok <= $batasEws);

        $pdf = Pdf::loadView('owner.debugger_pdf', compact(
            'hasil', 'cabangName', 'barangName', 'rangeDays', 
            'jumlahStok', 'safetyStockPercent', 'safetyStock', 
            'rekomendasiRestok', 'batasEws', 'leadTime', 'klasifikasiFsn', 'isKritis', 'dailyForecast'
        ));
        
        return $pdf->stream('Laporan_SES_Debugger_'.date('YmdHis').'.pdf');
    }

    public function updateBatasEws(Request $request)
    {
        $batasEws = $request->input('batas_ews', []);
        $safetyStockPercent = $request->input('safety_stock_percent', []);
        $hariPeramalan = $request->input('hari_peramalan', []);
        $cabangId = $request->input('target_cabang_id'); // 'gudang', 'all_cabang', or numeric id

        $realCabangId = ($cabangId === 'gudang' || empty($cabangId) || $cabangId === 'all_cabang') ? null : $cabangId;

        // Jika 'all_cabang', maka kita update ke semua cabang fisik yang ada
        if ($cabangId === 'all_cabang') {
            $semuaCabangId = \App\Models\Cabang::pluck('id')->toArray();
        } else {
            $semuaCabangId = [$realCabangId]; // Bisa array berisi null (untuk gudang) atau array ID cabang
        }

        foreach ($batasEws as $barangId => $batas) {
            $safety = $safetyStockPercent[$barangId] ?? 20;
            $hari = $hariPeramalan[$barangId] ?? 31;
            
            foreach ($semuaCabangId as $cId) {
                \App\Models\PengaturanEws::updateOrCreate(
                    ['barang_id' => $barangId, 'cabang_id' => $cId],
                    ['batas_ews' => $batas, 'safety_stock_percent' => $safety, 'hari_peramalan' => $hari]
                );
            }
        }

        return redirect()->back()->with('success', 'Pengaturan EWS & Peramalan berhasil diperbarui untuk target yang dipilih.');
    }

    public function getEwsSettings(Request $request)
    {
        $cabangId = $request->cabang_id;
        if ($cabangId === 'all_cabang') {
            return response()->json([]);
        }

        $realCabangId = ($cabangId === 'gudang' || empty($cabangId)) ? null : $cabangId;

        $settings = \App\Models\PengaturanEws::where('cabang_id', $realCabangId)
            ->get()->keyBy('barang_id');

        return response()->json($settings);
    }

    public function index(\App\Services\EwsService $ewsService)
    {
        // 1. Data Grafik Garis (Tren Omzet Bulanan)
        $transaksis = \App\Models\Transaksi::select('tanggal_transaksi', 'total_tagihan')->get();
        
        $omzetBulanan = $transaksis->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->tanggal_transaksi)->format('Y-m');
        })->map(function($row) {
            return $row->sum('total_tagihan');
        })->sortKeys();

        $labels = $omzetBulanan->keys()->map(function($b) {
            return \Carbon\Carbon::parse($b . '-01')->translatedFormat('F Y');
        });
        $dataOmzet = $omzetBulanan->values();

        // 2. Data Pemantauan Cabang (Grid & Early Warning)
        $cabangs = \App\Models\Cabang::with(['aplikasiLayanan'])->get();
        $peringatanCabang = [];
        
        // 3. EWS Dinamis Berbasis AI Buffer (Gudang Pusat & Seluruh Cabang)
        $allKritis = $ewsService->getAllKritisItems(30);
        $ewsAlerts = [];
        $totalBarangKritisGudang = 0;
        $cabangsWithKritis = [];

        foreach ($allKritis as $kritisItem) {
            $lokasiKey = $kritisItem['tipe_lokasi'] === 'Gudang Pusat' ? 'Gudang Pusat' : 'Cabang: ' . $kritisItem['lokasi_nama'];
            if (!isset($ewsAlerts[$lokasiKey])) {
                $ewsAlerts[$lokasiKey] = [];
            }
            $ewsAlerts[$lokasiKey][] = (object) $kritisItem;

            if ($kritisItem['tipe_lokasi'] === 'Gudang Pusat') {
                $totalBarangKritisGudang++;
            } else {
                if ($kritisItem['cabang_id']) {
                    $cabangsWithKritis[$kritisItem['cabang_id']] = true;
                }
            }
        }
        $totalCabangKritis = count($cabangsWithKritis);

        foreach ($cabangs as $cabang) {
            $peringatanCabang[$cabang->id] = isset($cabangsWithKritis[$cabang->id]);
        }

        // 4. Financial Dashboard Logic (Net Worth & Live Monitoring)
        $totalCashCabang = $cabangs->sum('saldo_cash_sekarang');
        $setoranMenggantung = \App\Models\SetoranClosing::where('status', 'menunggu_konfirmasi')->sum('nominal');
        $kasPusat = \App\Models\KasPusat::first();
        $totalKasPusat = $kasPusat ? $kasPusat->saldo : 0;
        $totalDigitalCabang = \App\Models\SaldoApkLayanan::sum('saldo_sekarang');
        $totalDigitalPusat = \App\Models\PusatAplikasiLayanan::sum('saldo_sekarang');
        
        $netWorth = $totalCashCabang + $setoranMenggantung + $totalKasPusat + $totalDigitalCabang + $totalDigitalPusat;

        // 5. Top & Worst Selling Items (1 Bulan Terakhir)
        $oneMonthAgo = \Carbon\Carbon::now()->subMonth()->startOfDay();
        $salesData = DB::table('detail_transaksi')
            ->join('master_barang', 'detail_transaksi.barang_id', '=', 'master_barang.id')
            ->join('transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')
            ->whereIn('master_barang.tipe_barang', ['Fisik ACC', 'Fisik Voucher Kartu'])
            ->where('transaksi.tanggal_transaksi', '>=', $oneMonthAgo)
            ->select('master_barang.nama_barang', DB::raw('SUM(detail_transaksi.kuantitas) as total_qty'))
            ->groupBy('master_barang.id', 'master_barang.nama_barang')
            ->orderByDesc('total_qty')
            ->get();

        $topSelling = $salesData->take(5);
        $worstSelling = $salesData->sortBy('total_qty')->take(5)->values(); // Reindex after sort

        return view('dashboards.owner', compact(
            'labels', 'dataOmzet', 'cabangs', 'peringatanCabang',
            'ewsAlerts', 'totalBarangKritisGudang', 'totalCabangKritis',
            'totalCashCabang', 'setoranMenggantung', 'totalKasPusat', 
            'totalDigitalCabang', 'totalDigitalPusat', 'netWorth',
            'topSelling', 'worstSelling'
        ));
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            DB::beginTransaction();

            // Opsional: Kosongkan data lama jika import adalah data baru (Tergantung business logic, disini kita append/tambah data)
            // DataHistorisAgregasi::truncate();

            Excel::import(new DataHistorisImport, $request->file('file_excel'));

            DB::commit();

            return redirect()->route('owner.dashboard')->with('success', 'Data Historis berhasil diimpor!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }

    public function jalankanPrediksi(SesForecastingService $sesService)
    {
        try {
            // Bersihkan prediksi lama sebelum hitung ulang
            HasilPrediksiSes::truncate();

            // Jalankan algoritma
            $sesService->calculateForecasting();

            return redirect()->route('owner.dashboard')->with('success', 'Perhitungan Single Exponential Smoothing (SES) selesai dijalankan. Hasil prediksi telah diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menjalankan prediksi SES: ' . $e->getMessage());
        }
    }
}
