<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Cabang;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanOmzetExport;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $cabangs = Cabang::all();
        
        $query = Transaksi::with(['cabang', 'kasir'])->orderBy('tanggal_transaksi', 'desc');

        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_transaksi', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_transaksi', '<=', $request->end_date);
        }
        
        if ($request->filled('cabang_id')) {
            $query->where('cabang_id', $request->cabang_id);
        }

        $transaksis = $query->paginate(20);
        
        // Simpan filter untuk passing ke export
        $filters = $request->only(['start_date', 'end_date', 'cabang_id']);

        return view('laporan.index', compact('cabangs', 'transaksis', 'filters'));
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'cabang_id']);
        return Excel::download(new LaporanOmzetExport($filters), 'Laporan_Omzet_' . date('Y-m-d') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $query = Transaksi::with(['cabang', 'kasir'])->orderBy('tanggal_transaksi', 'desc');

        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_transaksi', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_transaksi', '<=', $request->end_date);
        }
        
        if ($request->filled('cabang_id')) {
            $query->where('cabang_id', $request->cabang_id);
        }

        $transaksis = $query->get();
        $totalOmzet = $transaksis->sum('total_tagihan');
        $filters = $request->only(['start_date', 'end_date', 'cabang_id']);

        $pdf = Pdf::loadView('laporan.pdf', compact('transaksis', 'totalOmzet', 'filters'));
        
        return $pdf->download('Laporan_Omzet_' . date('Y-m-d') . '.pdf');
    }
}
