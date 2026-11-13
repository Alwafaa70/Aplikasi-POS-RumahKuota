<?php

namespace App\Http\Controllers;

use App\Models\PusatAplikasiLayanan;
use App\Models\KasPusat;
use App\Models\SetoranDompetPusat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\RiwayatApkService;

class AdminDompetPusatController extends Controller
{
    public function index()
    {
        $pusatLayanan = PusatAplikasiLayanan::with('masterAplikasiLayanan')->get();
        $kasPusat = KasPusat::first();

        // Ambil riwayat setoran (Pending Owner, Pending Admin, Selesai, Ditolak, Batal)
        $setorans = SetoranDompetPusat::with(['dompet.masterAplikasiLayanan', 'admin'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.dompet_pusat.index', compact('pusatLayanan', 'kasPusat', 'setorans'));
    }

    public function setorKasPusat(Request $request)
    {
        $request->validate([
            'nominal' => 'required|numeric|min:1',
            'catatan' => 'nullable|string|max:500',
            'bukti_transfer' => 'required|image|mimes:jpeg,png,jpg|max:2048' // Admin wajib upload bukti saat minta
        ]);

        $path = $request->file('bukti_transfer')->store('setoran_dompet_pusat', 'public');

        SetoranDompetPusat::create([
            'pusat_aplikasi_layanan_id' => null,
            'nominal' => $request->nominal,
            'jenis_pengaju' => 'admin',
            'status' => 'pending_owner',
            'catatan_pengaju' => $request->catatan,
            'bukti_transfer' => $path,
            'admin_id' => auth()->id()
        ]);

        return back()->with('success', 'Setoran Kas Pusat berhasil dibuat dan menunggu konfirmasi Owner.');
    }

    public function setorDigitalPusat(Request $request, $id)
    {
        $request->validate([
            'nominal' => 'required|numeric|min:1',
            'catatan' => 'nullable|string|max:500',
            'bukti_transfer' => 'required|image|mimes:jpeg,png,jpg|max:2048' // Admin wajib upload bukti saat minta
        ]);

        $dompet = PusatAplikasiLayanan::findOrFail($id);

        $path = $request->file('bukti_transfer')->store('setoran_dompet_pusat', 'public');

        SetoranDompetPusat::create([
            'pusat_aplikasi_layanan_id' => $dompet->id,
            'nominal' => $request->nominal,
            'jenis_pengaju' => 'admin',
            'status' => 'pending_owner',
            'catatan_pengaju' => $request->catatan,
            'bukti_transfer' => $path,
            'admin_id' => auth()->id()
        ]);

        return back()->with('success', 'Setoran Digital Pusat berhasil dibuat dan menunggu konfirmasi Owner.');
    }

    public function konfirmasiSetoranOwner(Request $request, $id)
    {
        $request->validate([
            'bukti_transfer' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'catatan_konfirmasi' => 'nullable|string|max:500'
        ]);

        $setoran = SetoranDompetPusat::findOrFail($id);

        if ($setoran->status !== 'pending_admin') {
            return back()->with('error', 'Setoran tidak dalam status menunggu admin.');
        }

        $path = $request->file('bukti_transfer')->store('setoran_dompet_pusat', 'public');

        DB::transaction(function () use ($request, $setoran, $path) {
            // Potong saldo
            if ($setoran->pusat_aplikasi_layanan_id === null) {
                // Kas Fisik
                $kas = KasPusat::first();
                if (!$kas || $kas->saldo < $setoran->nominal) {
                    throw new \Exception('Saldo Kas Pusat tidak mencukupi untuk dikonfirmasi.');
                }
                $kas->decrement('saldo', $setoran->nominal);
            } else {
                // Digital
                $dompet = PusatAplikasiLayanan::find($setoran->pusat_aplikasi_layanan_id);
                if (!$dompet || $dompet->saldo_sekarang < $setoran->nominal) {
                    throw new \Exception('Saldo Digital tidak mencukupi untuk dikonfirmasi.');
                }
                $dompet->decrement('saldo_sekarang', $setoran->nominal);

                RiwayatApkService::catat(
                    'pusat', 
                    $dompet->id, 
                    'keluar', 
                    $setoran->nominal, 
                    'Tarik Dana / Setoran ke Owner', 
                    null
                );
            }

            $setoran->update([
                'status' => 'selesai',
                'bukti_transfer' => $path,
                'catatan_konfirmasi' => $request->catatan_konfirmasi,
                'admin_id' => auth()->id()
            ]);
        });

        return back()->with('success', 'Setoran dari Owner berhasil dikonfirmasi dan Saldo telah dipotong.');
    }

    public function tolakSetoranOwner(Request $request, $id)
    {
        $request->validate([
            'catatan_konfirmasi' => 'required|string|max:500' // Wajib diisi alasan penolakan
        ]);

        $setoran = SetoranDompetPusat::findOrFail($id);

        if ($setoran->status !== 'pending_admin') {
            return back()->with('error', 'Setoran tidak dalam status menunggu admin.');
        }

        $setoran->update([
            'status' => 'ditolak',
            'catatan_konfirmasi' => $request->catatan_konfirmasi,
            'admin_id' => auth()->id()
        ]);

        return back()->with('success', 'Setoran dari Owner berhasil ditolak.');
    }

    public function batalSetoranAdmin($id)
    {
        $setoran = SetoranDompetPusat::findOrFail($id);

        if ($setoran->status !== 'pending_owner') {
            return back()->with('error', 'Setoran tidak bisa dibatalkan.');
        }

        if ($setoran->jenis_pengaju !== 'admin' || $setoran->admin_id !== auth()->id()) {
            return back()->with('error', 'Anda tidak berhak membatalkan setoran ini.');
        }

        $setoran->update([
            'status' => 'dibatalkan'
        ]);

        return back()->with('success', 'Permintaan setoran yang Anda buat berhasil dibatalkan.');
    }
}
