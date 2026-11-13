<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SetoranClosing;
use App\Models\KasPusat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SetoranPusatController extends Controller
{
    public function index()
    {
        $setorans = SetoranClosing::with(['cabang', 'laporanClosing.kasir', 'admin'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Ambil total kas pusat
        $kasPusat = KasPusat::first();
        $totalKas = $kasPusat ? $kasPusat->saldo : 0;

        return view('admin.setoran.index', compact('setorans', 'totalKas'));
    }

    public function confirm(Request $request, $id)
    {
        $setoran = SetoranClosing::findOrFail($id);
        
        if ($setoran->status !== 'menunggu_konfirmasi') {
            return back()->with('error', 'Status setoran ini sudah diproses sebelumnya.');
        }

        DB::beginTransaction();
        try {
            // Update status setoran
            $setoran->update([
                'status' => 'diterima',
                'dikonfirmasi_oleh' => Auth::id(),
                'waktu_dikonfirmasi' => now(),
            ]);

            // Tambahkan nominal ke Kas Pusat
            $kasPusat = KasPusat::firstOrCreate(['id' => 1], ['saldo' => 0]);
            $kasPusat->increment('saldo', $setoran->nominal);

            DB::commit();
            return back()->with('success', 'Setoran berhasil dikonfirmasi dan uang telah masuk ke Kas Pusat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses konfirmasi setoran: ' . $e->getMessage());
        }
    }
}
