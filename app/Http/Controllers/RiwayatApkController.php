<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PusatAplikasiLayanan;
use App\Models\CabangAplikasiLayanan;
use App\Models\RiwayatAplikasiLayanan;

class RiwayatApkController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type'); // 'pusat' atau 'cabang'
        $id = $request->query('id'); // ID dari PusatAplikasiLayanan atau CabangAplikasiLayanan

        if (!in_array($type, ['pusat', 'cabang']) || !$id) {
            return redirect()->back()->with('error', 'Parameter tidak valid.');
        }

        // Fetch APK Model
        $apkModel = null;
        if ($type === 'pusat') {
            $apkModel = PusatAplikasiLayanan::with('masterAplikasiLayanan')->findOrFail($id);
            $namaLayanan = $apkModel->masterAplikasiLayanan->nama_layanan . ' (Pusat)';
        } else {
            $apkModel = CabangAplikasiLayanan::with(['masterAplikasiLayanan', 'cabang'])->findOrFail($id);
            $namaLayanan = $apkModel->masterAplikasiLayanan->nama_layanan . ' (' . $apkModel->cabang->nama_cabang . ')';
        }

        // Fetch Riwayat
        $riwayat = RiwayatAplikasiLayanan::where('jenis_apk', $type)
            ->where('apk_layanan_id', $id)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('owner.riwayat_apk.index', compact('apkModel', 'namaLayanan', 'riwayat', 'type'));
    }
}
