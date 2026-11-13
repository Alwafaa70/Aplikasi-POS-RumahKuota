<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LaporanClosing;
use App\Models\Cabang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\KasPusat;
use App\Models\SetoranClosing;

class AdminClosingController extends Controller
{
    public function index(Request $request)
    {
        $query = LaporanClosing::with(['kasir', 'cabang'])->orderBy('created_at', 'desc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('cabang_id') && $request->cabang_id != 'all') {
            $query->where('cabang_id', $request->cabang_id);
        }

        $closings = $query->paginate(20)->withQueryString();
        $cabangs = Cabang::all();

        return view('admin.closing.index', compact('closings', 'cabangs'));
    }

    public function show($id)
    {
        $closing = LaporanClosing::with(['kasir', 'cabang', 'details.masterAplikasiLayanan'])->findOrFail($id);
        return view('admin.closing.show', compact('closing'));
    }

    public function konfirmasi(Request $request, $id)
    {
        $request->validate([
            'catatan_admin' => 'nullable|string'
        ]);

        $closing = LaporanClosing::findOrFail($id);
        
        if ($closing->status_konfirmasi === 'dikonfirmasi') {
            return back()->with('error', 'Laporan ini sudah dikonfirmasi sebelumnya.');
        }

        DB::beginTransaction();
        try {
            $closing->update([
                'status_konfirmasi' => 'dikonfirmasi',
                'catatan_admin' => $request->catatan_admin
            ]);

            // Mutasi Kas:
            // 1. Cabang cash is reset to saldo_awal_cash
            $cabang = Cabang::find($closing->cabang_id);
            $modalAwal = $cabang->saldo_awal_cash ?? 0;
            
            // Nominal disetor adalah aktual fisik dikurangi modal awal
            $nominalSetor = max(0, $closing->uang_fisik_aktual - $modalAwal);
            
            $cabang->update([
                'saldo_cash_sekarang' => $modalAwal
            ]);

            // 2. Tambah nominal ke Kas Pusat
            if ($nominalSetor > 0) {
                $kasPusat = KasPusat::firstOrCreate(['id' => 1], ['saldo' => 0]);
                $kasPusat->increment('saldo', $nominalSetor);

                // 3. Update SetoranClosing jika ada
                $setoran = SetoranClosing::where('laporan_closing_id', $closing->id)->first();
                if ($setoran) {
                    $setoran->update([
                        'status' => 'diterima',
                        'dikonfirmasi_oleh' => Auth::id(),
                        'waktu_dikonfirmasi' => now(),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.closing.index')->with('success', 'Laporan closing berhasil dikonfirmasi dan Saldo Kas telah dimutasi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengonfirmasi closing: ' . $e->getMessage());
        }
    }

    public function exportPdf(Request $request)
    {
        $query = LaporanClosing::with(['kasir', 'cabang', 'details.masterAplikasiLayanan'])->where('status_konfirmasi', 'dikonfirmasi')->orderBy('created_at', 'desc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('cabang_id') && $request->cabang_id != 'all') {
            $query->where('cabang_id', $request->cabang_id);
        }

        $closings = $query->get();

        $pdf = Pdf::loadView('admin.closing.pdf', compact('closings'));
        return $pdf->download('Laporan_Riwayat_Closing.pdf');
    }
}
