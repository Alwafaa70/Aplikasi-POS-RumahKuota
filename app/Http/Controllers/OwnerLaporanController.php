<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Pembelian;
use App\Models\LaporanClosing;
use App\Models\SetoranDompetPusat;
use App\Models\StokPusat;
use App\Models\StokCabangBatch;
use App\Models\BarangMasuk;
use App\Models\KasPusat;
use App\Models\RiwayatAplikasiLayanan;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\MutasiStok;
use App\Models\RequestOperasional;
use App\Models\Cabang;
use Carbon\Carbon;

class OwnerLaporanController extends Controller
{
    public function index()
    {
        return view('owner.pusat_laporan.index');
    }

    public function transaksiKasir(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());
        $cabangId  = $request->query('cabang_id', '');

        $query = Transaksi::with(['kasir', 'cabang', 'detailTransaksi.barang.category'])
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate]);

        if ($cabangId) {
            $query->where('cabang_id', $cabangId);
        }

        $transaksis = $query->orderBy('created_at', 'desc')->paginate(20);
        $cabangs = Cabang::orderBy('nama_cabang')->get();

        return view('owner.pusat_laporan.transaksi', compact('transaksis', 'startDate', 'endDate', 'cabangId', 'cabangs'));
    }

    public function transaksiPdf(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());
        $cabangId  = $request->query('cabang_id', '');

        $query = Transaksi::with(['kasir', 'cabang', 'detailTransaksi.barang.category'])
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate]);

        if ($cabangId) {
            $query->where('cabang_id', $cabangId);
        }

        $transaksis = $query->orderBy('created_at', 'desc')->get();
        $cabang = $cabangId ? Cabang::find($cabangId) : null;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('owner.pusat_laporan.transaksi_pdf', compact('transaksis', 'startDate', 'endDate', 'cabang'));
        return $pdf->download('Laporan_Transaksi_Kasir_' . $startDate . '_sd_' . $endDate . '.pdf');
    }
    
    public function strukKasir($id)
    {
        $transaksi = Transaksi::with(['kasir', 'cabang', 'detailTransaksi.barang'])->findOrFail($id);
        
        // Cek jika kasir yang menangani null
        if (!$transaksi->kasir) {
            return redirect()->back()->with('error', 'Petugas Kasir tidak ditemukan untuk transaksi ini.');
        }

        // Tampilkan view struk
        return view('kasir.pos.struk_qris', compact('transaksi'));
    }

    public function pembelianSupplier(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());

        $pembelians = Pembelian::with(['supplier', 'admin', 'detailPembelian.barangFisik'])
            ->whereBetween('tanggal_pembelian', [$startDate, $endDate])
            ->orderBy('tanggal_pembelian', 'desc')
            ->paginate(15);

        return view('owner.pusat_laporan.pembelian', compact('pembelians', 'startDate', 'endDate'));
    }

    public function mutasiStok(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());
        $cabangId  = $request->query('cabang_id', '');

        $query = MutasiStok::with(['cabangAsal', 'cabangTujuan', 'detailMutasi.barang', 'admin'])
            ->whereBetween('tanggal_mutasi', [$startDate, $endDate]);

        if ($cabangId) {
            $query->where(function($q) use ($cabangId) {
                $q->where('cabang_asal_id', $cabangId)
                  ->orWhere('cabang_tujuan_id', $cabangId);
            });
        }

        $mutasis = $query->orderBy('tanggal_mutasi', 'desc')->paginate(15);
        $cabangs = Cabang::orderBy('nama_cabang')->get();

        return view('owner.pusat_laporan.mutasi', compact('mutasis', 'startDate', 'endDate', 'cabangId', 'cabangs'));
    }

    public function requestOperasional(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());
        $cabangId  = $request->query('cabang_id', '');

        $query = RequestOperasional::with(['kasir', 'cabang', 'detail.barangFisik', 'transaksi', 'admin', 'masterAplikasiLayanan'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($cabangId) {
            $query->where('cabang_id', $cabangId);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(20);
        $cabangs = Cabang::orderBy('nama_cabang')->get();

        return view('owner.pusat_laporan.request_operasional', compact('requests', 'startDate', 'endDate', 'cabangId', 'cabangs'));
    }

    public function closingCabang(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());
        $cabangId  = $request->query('cabang_id', '');

        $query = LaporanClosing::with(['kasir', 'cabang', 'details.masterAplikasiLayanan'])
            ->whereBetween('tanggal', [$startDate, $endDate]);

        if ($cabangId) {
            $query->where('cabang_id', $cabangId);
        }

        $closings = $query->orderBy('tanggal', 'desc')->paginate(15);
        $cabangs = Cabang::orderBy('nama_cabang')->get();

        return view('owner.pusat_laporan.closing', compact('closings', 'startDate', 'endDate', 'cabangId', 'cabangs'));
    }

    // ==========================================
    // 1. Laporan Riwayat Setoran
    // ==========================================
    public function riwayatSetoran(Request $request)
    {
        $query = SetoranDompetPusat::with(['dompet.masterAplikasiLayanan', 'admin'])->orderBy('created_at', 'desc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $setorans = $query->paginate(20);
        return view('owner.pusat_laporan.riwayat_setoran', compact('setorans'));
    }

    public function riwayatSetoranPdf(Request $request)
    {
        $query = SetoranDompetPusat::with(['dompet.masterAplikasiLayanan', 'admin'])->orderBy('created_at', 'desc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $setorans = $query->get();
        $pdf = Pdf::loadView('owner.pusat_laporan.riwayat_setoran_pdf', compact('setorans', 'request'));
        return $pdf->stream('Laporan_Riwayat_Setoran.pdf');
    }

    // ==========================================
    // 2. Laporan Aset & Stok Barang
    // ==========================================
    public function asetStok(Request $request)
    {
        $asetPusat = collect();
        if (!$request->filled('lokasi') || $request->lokasi == 'pusat') {
            $asetPusat = BarangMasuk::with(['barang', 'pembelian'])
                ->where('stok_sisa', '>', 0)
                ->get()
                ->map(function ($item) {
                    $hpp = $item->qty_beli > 0 ? ($item->total_harga_beli / $item->qty_beli) : 0;
                    $usia_hari = (int) floor(\Carbon\Carbon::parse($item->created_at)->diffInDays(\Carbon\Carbon::now()));
                    $nilai_aset = $item->stok_sisa * $hpp;
                    return (object)[
                        'nama_barang' => $item->barang->nama_barang ?? 'Unknown',
                        'batch_id' => 'BCH-' . $item->id,
                        'tanggal_masuk' => $item->created_at,
                        'stok' => $item->stok_sisa,
                        'hpp' => $hpp,
                        'usia_hari' => $usia_hari,
                        'nilai_aset' => $nilai_aset,
                        'lokasi' => 'Gudang Pusat'
                    ];
                });
        }

        $asetCabang = collect();
        if (!$request->filled('lokasi') || $request->lokasi != 'pusat') {
            $query = StokCabangBatch::with(['barang', 'cabang', 'barangMasuk'])
                ->where('stok_sisa_cabang', '>', 0);
                
            if ($request->filled('lokasi') && $request->lokasi != 'pusat') {
                $query->where('cabang_id', $request->lokasi);
            }
            
            $asetCabang = $query->get()->map(function ($item) {
                $usia_hari = (int) floor(\Carbon\Carbon::parse($item->created_at)->diffInDays(\Carbon\Carbon::now()));
                $nilai_aset = $item->stok_sisa_cabang * $item->hpp_batch;
                return (object)[
                    'nama_barang' => $item->barang->nama_barang ?? 'Unknown',
                    'batch_id' => 'BCH-' . $item->barang_masuk_id,
                    'tanggal_masuk' => $item->created_at,
                    'stok' => $item->stok_sisa_cabang,
                    'hpp' => $item->hpp_batch,
                    'usia_hari' => $usia_hari,
                    'nilai_aset' => $nilai_aset,
                    'lokasi' => 'Cabang: ' . ($item->cabang->nama_cabang ?? 'Unknown')
                ];
            });
        }

        $semuaAset = $asetPusat->concat($asetCabang);

        if ($request->filled('sort')) {
            if ($request->sort == 'terlama') {
                $semuaAset = $semuaAset->sortByDesc('usia_hari');
            } elseif ($request->sort == 'terbaru') {
                $semuaAset = $semuaAset->sortBy('usia_hari');
            }
        } else {
            $semuaAset = $semuaAset->sortBy('usia_hari');
        }

        $cabangs = \App\Models\Cabang::all();

        return view('owner.pusat_laporan.aset_stok', compact('semuaAset', 'cabangs'));
    }

    public function asetStokPdf(Request $request)
    {
        $asetPusat = collect();
        if (!$request->filled('lokasi') || $request->lokasi == 'pusat') {
            $asetPusat = BarangMasuk::with(['barang', 'pembelian'])
                ->where('stok_sisa', '>', 0)
                ->get()
                ->map(function ($item) {
                    $hpp = $item->qty_beli > 0 ? ($item->total_harga_beli / $item->qty_beli) : 0;
                    $usia_hari = (int) floor(\Carbon\Carbon::parse($item->created_at)->diffInDays(\Carbon\Carbon::now()));
                    $nilai_aset = $item->stok_sisa * $hpp;
                    return (object)[
                        'nama_barang' => $item->barang->nama_barang ?? 'Unknown',
                        'batch_id' => 'BCH-' . $item->id,
                        'tanggal_masuk' => $item->created_at,
                        'stok' => $item->stok_sisa,
                        'hpp' => $hpp,
                        'usia_hari' => $usia_hari,
                        'nilai_aset' => $nilai_aset,
                        'lokasi' => 'Gudang Pusat'
                    ];
                });
        }

        $asetCabang = collect();
        if (!$request->filled('lokasi') || $request->lokasi != 'pusat') {
            $query = StokCabangBatch::with(['barang', 'cabang', 'barangMasuk'])
                ->where('stok_sisa_cabang', '>', 0);
                
            if ($request->filled('lokasi') && $request->lokasi != 'pusat') {
                $query->where('cabang_id', $request->lokasi);
            }
            
            $asetCabang = $query->get()->map(function ($item) {
                $usia_hari = (int) floor(\Carbon\Carbon::parse($item->created_at)->diffInDays(\Carbon\Carbon::now()));
                $nilai_aset = $item->stok_sisa_cabang * $item->hpp_batch;
                return (object)[
                    'nama_barang' => $item->barang->nama_barang ?? 'Unknown',
                    'batch_id' => 'BCH-' . $item->barang_masuk_id,
                    'tanggal_masuk' => $item->created_at,
                    'stok' => $item->stok_sisa_cabang,
                    'hpp' => $item->hpp_batch,
                    'usia_hari' => $usia_hari,
                    'nilai_aset' => $nilai_aset,
                    'lokasi' => 'Cabang: ' . ($item->cabang->nama_cabang ?? 'Unknown')
                ];
            });
        }

        $semuaAset = $asetPusat->concat($asetCabang);

        if ($request->filled('sort')) {
            if ($request->sort == 'terlama') {
                $semuaAset = $semuaAset->sortByDesc('usia_hari');
            } elseif ($request->sort == 'terbaru') {
                $semuaAset = $semuaAset->sortBy('usia_hari');
            }
        } else {
            $semuaAset = $semuaAset->sortBy('usia_hari');
        }

        $pdf = Pdf::loadView('owner.pusat_laporan.aset_stok_pdf', compact('semuaAset'));
        return $pdf->stream('Laporan_Aset_Stok.pdf');
    }

    // ==========================================
    // 3. Laporan Keuangan Global
    // ==========================================
    public function keuanganGlobal(Request $request)
    {
        $data = $this->hitungKeuanganGlobal($request);
        return view('owner.pusat_laporan.keuangan_global', $data);
    }

    public function keuanganGlobalPdf(Request $request)
    {
        $data = $this->hitungKeuanganGlobal($request);
        $pdf = Pdf::loadView('owner.pusat_laporan.keuangan_global_pdf', $data);
        $pdf->setPaper('a4', 'landscape');
        return $pdf->stream('Laporan_Keuangan_Global.pdf');
    }

    private function hitungKeuanganGlobal(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));
        $start = $startDate . ' 00:00:00';
        $end = $endDate . ' 23:59:59';

        // 1. Kas Fisik Pusat
        $kasFisikAwal = 0; // Tidak ada ledger riwayat untuk kas fisik pusat saat ini
        $pemasukanFisik = 0;
        $pengeluaranFisik = 0;
        
        $kasPusat = \App\Models\KasPusat::first();
        $kasFisikAkhir = $kasPusat ? $kasPusat->saldo : 0;
        $mutasiFisik = collect(); // Kosong karena tidak ada tabel RiwayatKasPusat

        // 2. Dompet Digital
        $dompets = \App\Models\PusatAplikasiLayanan::with('masterAplikasiLayanan')->get();
        $rekapDompet = [];
        $totalDompetAwal = 0;
        $totalDompetPemasukan = 0;
        $totalDompetPengeluaran = 0;
        $totalDompetAkhir = 0;

        foreach ($dompets as $dompet) {
            $awal = RiwayatAplikasiLayanan::where('jenis_apk', 'pusat')
                ->where('apk_layanan_id', $dompet->id)
                ->where('created_at', '<', $start)
                ->orderBy('created_at', 'desc')
                ->first()->saldo_akhir ?? 0;

            $mutasiDigital = RiwayatAplikasiLayanan::where('jenis_apk', 'pusat')
                ->where('apk_layanan_id', $dompet->id)
                ->whereBetween('created_at', [$start, $end])
                ->orderBy('created_at', 'asc')
                ->get();
            
            $masuk = $mutasiDigital->where('jenis_mutasi', 'masuk')->sum('nominal');
            $keluar = $mutasiDigital->where('jenis_mutasi', 'keluar')->sum('nominal');
            $akhir = $mutasiDigital->last()->saldo_akhir ?? $awal;

            $totalDompetAwal += $awal;
            $totalDompetPemasukan += $masuk;
            $totalDompetPengeluaran += $keluar;
            $totalDompetAkhir += $akhir;

            $rekapDompet[] = (object)[
                'nama' => $dompet->masterAplikasiLayanan->nama_layanan ?? 'Unknown',
                'rekening' => $dompet->nomor_rekening,
                'awal' => $awal,
                'masuk' => $masuk,
                'keluar' => $keluar,
                'akhir' => $akhir,
                'mutasi' => $mutasiDigital
            ];
        }

        return compact(
            'startDate', 'endDate',
            'kasFisikAwal', 'pemasukanFisik', 'pengeluaranFisik', 'kasFisikAkhir', 'mutasiFisik',
            'rekapDompet',
            'totalDompetAwal', 'totalDompetPemasukan', 'totalDompetPengeluaran', 'totalDompetAkhir'
        );
    }
}
