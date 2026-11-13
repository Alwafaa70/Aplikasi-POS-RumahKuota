<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SetoranClosing;

class LaporanSetoranController extends Controller
{
    public function index(Request $request)
    {
        $query = SetoranClosing::with(['cabang', 'laporanClosing.kasir', 'admin', 'pusatAplikasiLayanan.masterAplikasiLayanan'])
            ->orderBy('created_at', 'desc');

        // Filter Jenis Setoran
        if ($request->filled('jenis')) {
            if ($request->jenis === 'cabang_ke_pusat') {
                $query->whereNotNull('cabang_id');
            } elseif ($request->jenis === 'pusat_ke_owner') {
                $query->whereNull('cabang_id');
            }
        }

        // Filter Tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $setoran = $query->paginate(50)->withQueryString();

        return view('owner.laporan_setoran.index', compact('setoran'));
    }
}
