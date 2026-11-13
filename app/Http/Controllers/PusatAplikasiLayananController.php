<?php

namespace App\Http\Controllers;

use App\Models\PusatAplikasiLayanan;
use App\Models\MasterAplikasiLayanan;
use App\Models\SetoranDompetPusat;
use App\Models\KasPusat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\RiwayatApkService;

class PusatAplikasiLayananController extends Controller
{
    public function index()
    {
        $pusatLayanan = PusatAplikasiLayanan::with('masterAplikasiLayanan')->get();
        $masterLayanan = MasterAplikasiLayanan::orderBy('nama_layanan')->get();
        $qrisMaster = PusatAplikasiLayanan::where('is_qris_tujuan', true)->first();
        $kasPusat = KasPusat::first();

        // Ambil riwayat setoran
        $setorans = SetoranDompetPusat::with(['dompet.masterAplikasiLayanan', 'admin'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pusat_aplikasi_layanan.index', compact('pusatLayanan', 'masterLayanan', 'qrisMaster', 'kasPusat', 'setorans'));
    }

    public function initKasPusat(Request $request)
    {
        $request->validate([
            'saldo_awal' => 'required|numeric|min:0'
        ]);

        $exists = KasPusat::first();
        if ($exists) {
            return back()->with('error', 'Kas Pusat sudah pernah diinisiasi sebelumnya!');
        }

        KasPusat::create([
            'saldo_awal' => $request->saldo_awal,
            'saldo'      => $request->saldo_awal,
        ]);

        return back()->with('success', 'Kas Fisik Pusat berhasil diinisiasi!');
    }

    public function store(Request $request)
    {
        $request->validate([
            'master_aplikasi_layanan_id' => 'required|exists:master_aplikasi_layanan,id',
            'nomor_rekening'             => 'required|string|max:100',
            'saldo_awal'                 => 'required|numeric|min:0',
        ]);
        
        $isFirst = PusatAplikasiLayanan::count() === 0;

        $dompet = PusatAplikasiLayanan::create([
            'master_aplikasi_layanan_id' => $request->master_aplikasi_layanan_id,
            'nomor_rekening'             => $request->nomor_rekening,
            'saldo_awal'                 => $request->saldo_awal,
            'saldo_sekarang'             => $request->saldo_awal,
            'is_qris_tujuan'             => $isFirst,
        ]);

        if ($request->saldo_awal > 0) {
            RiwayatApkService::catat(
                'pusat', 
                $dompet->id, 
                'masuk', 
                $request->saldo_awal, 
                'Saldo Awal Setup Dompet Pusat', 
                $dompet
            );
        }

        return back()->with('success', 'Dompet Pusat berhasil ditambahkan.');
    }

    public function setQrisTujuan($id)
    {
        $dompet = PusatAplikasiLayanan::findOrFail($id);
        PusatAplikasiLayanan::query()->update(['is_qris_tujuan' => false]);
        $dompet->update(['is_qris_tujuan' => true]);
        return redirect()->back()->with('success', 'Rekening ' . $dompet->nomor_rekening . ' berhasil dijadikan Tujuan QRIS Utama.');
    }

    public function topUp(Request $request, $id)
    {
        $request->validate([
            'nominal'    => 'required|numeric|min:1',
            'bukti_foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'catatan'    => 'nullable|string|max:500'
        ]);

        $dompet = PusatAplikasiLayanan::findOrFail($id);

        if ($request->hasFile('bukti_foto')) {
            $path = $request->file('bukti_foto')->store('topup_pusat', 'public');
        } else {
            return back()->with('error', 'Bukti foto wajib diunggah.');
        }

        DB::transaction(function () use ($request, $dompet, $path) {
            $dompet->increment('saldo_sekarang', $request->nominal);

            $topup = \App\Models\HistoryTopupPusat::create([
                'pusat_aplikasi_layanan_id' => $dompet->id,
                'admin_id'                  => auth()->id(),
                'nominal'                   => $request->nominal,
                'bukti_foto'                => $path,
                'catatan'                   => $request->catatan,
            ]);

            RiwayatApkService::catat(
                'pusat', 
                $dompet->id, 
                'masuk', 
                $request->nominal, 
                $request->catatan ?? 'Topup Manual', 
                $topup
            );
        });

        return back()->with('success', 'Top Up berhasil dan saldo telah bertambah.');
    }

    public function update(Request $request, $id)
    {
        $request->validate(['nomor_rekening' => 'required|string|max:100']);
        $dompet = PusatAplikasiLayanan::findOrFail($id);
        $dompet->update(['nomor_rekening' => $request->nomor_rekening]);
        return back()->with('success', 'Informasi Dompet Pusat berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $dompet = PusatAplikasiLayanan::findOrFail($id);
        $dompet->delete();
        return back()->with('success', 'Dompet Pusat berhasil dihapus.');
    }

    public function setorOwner(Request $request)
    {
        $request->validate([
            'nominal' => 'required|numeric|min:1',
            'catatan' => 'nullable|string|max:500'
        ]);

        $kasPusat = KasPusat::first();

        if (!$kasPusat || $kasPusat->saldo < $request->nominal) {
            return back()->with('error', 'Saldo Kas Pusat tidak mencukupi untuk nominal setoran ini.');
        }

        SetoranDompetPusat::create([
            'pusat_aplikasi_layanan_id' => null,
            'nominal' => $request->nominal,
            'jenis_pengaju' => 'owner',
            'status' => 'pending_admin',
            'catatan_pengaju' => $request->catatan
        ]);

        return back()->with('success', 'Request Setoran Kas Pusat berhasil dibuat dan menunggu Admin.');
    }

    public function setorDigitalOwner(Request $request, $id)
    {
        $request->validate([
            'nominal' => 'required|numeric|min:1',
            'catatan' => 'nullable|string|max:500'
        ]);

        $dompet = PusatAplikasiLayanan::findOrFail($id);

        if ($dompet->saldo_sekarang < $request->nominal) {
            return back()->with('error', 'Saldo dompet tidak mencukupi untuk nominal setoran ini.');
        }

        SetoranDompetPusat::create([
            'pusat_aplikasi_layanan_id' => $dompet->id,
            'nominal' => $request->nominal,
            'jenis_pengaju' => 'owner',
            'status' => 'pending_admin',
            'catatan_pengaju' => $request->catatan
        ]);

        return back()->with('success', 'Request Setoran Saldo Digital berhasil dibuat dan menunggu Admin.');
    }

    public function batalSetoranOwner($id)
    {
        $setoran = SetoranDompetPusat::findOrFail($id);

        if ($setoran->status !== 'pending_admin') {
            return back()->with('error', 'Setoran tidak bisa dibatalkan karena sudah diproses.');
        }

        if ($setoran->jenis_pengaju !== 'owner') {
            return back()->with('error', 'Anda tidak berhak membatalkan setoran ini.');
        }

        $setoran->update(['status' => 'dibatalkan']);
        return back()->with('success', 'Request setoran berhasil dibatalkan.');
    }

    public function terimaSetoranAdmin(Request $request, $id)
    {
        $setoran = SetoranDompetPusat::findOrFail($id);

        if ($setoran->status !== 'pending_owner') {
            return back()->with('error', 'Setoran tidak dalam status menunggu persetujuan Anda.');
        }

        DB::transaction(function () use ($setoran) {
            // Potong saldo
            if ($setoran->pusat_aplikasi_layanan_id === null) {
                $kas = KasPusat::first();
                if (!$kas || $kas->saldo < $setoran->nominal) {
                    throw new \Exception('Saldo Kas Pusat tidak mencukupi.');
                }
                $kas->decrement('saldo', $setoran->nominal);
            } else {
                $dompet = PusatAplikasiLayanan::find($setoran->pusat_aplikasi_layanan_id);
                if (!$dompet || $dompet->saldo_sekarang < $setoran->nominal) {
                    throw new \Exception('Saldo Digital tidak mencukupi.');
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

            $setoran->update(['status' => 'selesai']);
        });

        return back()->with('success', 'Setoran dari Admin berhasil Anda terima, saldo telah dipotong.');
    }

    public function tolakSetoranAdmin(Request $request, $id)
    {
        $request->validate([
            'catatan_konfirmasi' => 'required|string|max:500' // Alasan tolak
        ]);

        $setoran = SetoranDompetPusat::findOrFail($id);

        if ($setoran->status !== 'pending_owner') {
            return back()->with('error', 'Setoran tidak dalam status menunggu persetujuan Anda.');
        }

        $setoran->update([
            'status' => 'ditolak',
            'catatan_konfirmasi' => $request->catatan_konfirmasi
        ]);

        return back()->with('success', 'Setoran dari Admin berhasil Anda tolak.');
    }
}
