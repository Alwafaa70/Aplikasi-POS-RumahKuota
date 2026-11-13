<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cabang;

class CabangController extends Controller
{
    public function index()
    {
        $cabangs = Cabang::orderBy('id', 'asc')->get();
        $masterLayanan = \App\Models\MasterAplikasiLayanan::orderBy('nama_layanan')->get();
        return view('master_cabang.index', compact('cabangs', 'masterLayanan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_cabang' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'area' => 'required|string|max:100',
            'saldo_awal_cash' => 'required|numeric|min:0',
            'layanan' => 'nullable|array',
            'layanan.*.master_aplikasi_layanan_id' => 'required|exists:master_aplikasi_layanan,id',
            'layanan.*.nomor_rekening' => 'required|string',
            'layanan.*.saldo_awal' => 'required|numeric|min:0',
        ]);

        // Validasi dihapus karena is_qris_tujuan sudah pindah ke Master

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Buat Cabang
            $cabang = Cabang::create([
                'nama_cabang' => $request->nama_cabang,
                'alamat' => $request->alamat,
                'area' => $request->area,
                'saldo_awal_cash' => $request->saldo_awal_cash,
                'saldo_cash_sekarang' => $request->saldo_awal_cash,
            ]);

            // Tambahkan Layanan Dinamis
            if ($request->has('layanan')) {
                foreach ($request->layanan as $l) {
                    $cabang->aplikasiLayanan()->create([
                        'master_aplikasi_layanan_id' => $l['master_aplikasi_layanan_id'],
                        'nomor_rekening' => $l['nomor_rekening'],
                        'saldo_awal' => $l['saldo_awal'],
                        'saldo_sekarang' => $l['saldo_awal'],
                    ]);
                }
            }

            \Illuminate\Support\Facades\DB::commit();
            return back()->with('success', 'Cabang baru dan dompetnya berhasil dibuat!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage())->withInput();
        }
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_cabang' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'area' => 'required|string|max:100',
            'is_active' => 'required|boolean',
            'layanan' => 'nullable|array',
            'layanan.*.id' => 'nullable|exists:cabang_aplikasi_layanan,id',
            'layanan.*.master_aplikasi_layanan_id' => 'required|exists:master_aplikasi_layanan,id',
            'layanan.*.nomor_rekening' => 'required|string',
            'layanan.*.saldo_awal' => 'nullable|numeric|min:0',
        ]);

        $cabang = Cabang::findOrFail($id);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Update Cabang (DO NOT update saldo_awal_cash)
            $cabang->update([
                'nama_cabang' => $request->nama_cabang,
                'alamat' => $request->alamat,
                'area' => $request->area,
                'is_active' => $request->is_active,
            ]);

            // Sync Layanan
            $submittedIds = [];
            if ($request->has('layanan')) {
                foreach ($request->layanan as $l) {
                    if (isset($l['id']) && $l['id']) {
                        // Update existing (DO NOT update saldo_awal)
                        $layanan = $cabang->aplikasiLayanan()->find($l['id']);
                        if ($layanan) {
                            $layanan->update([
                                'master_aplikasi_layanan_id' => $l['master_aplikasi_layanan_id'],
                                'nomor_rekening' => $l['nomor_rekening'],
                            ]);
                            $submittedIds[] = $layanan->id;
                        }
                    } else {
                        // Create new
                        $newLay = $cabang->aplikasiLayanan()->create([
                            'master_aplikasi_layanan_id' => $l['master_aplikasi_layanan_id'],
                            'nomor_rekening' => $l['nomor_rekening'],
                            'saldo_awal' => $l['saldo_awal'],
                            'saldo_sekarang' => $l['saldo_awal'],
                        ]);
                        $submittedIds[] = $newLay->id;
                    }
                }
            }

            // Remove deleted layanan
            $cabang->aplikasiLayanan()->whereNotIn('id', $submittedIds)->delete();

            \Illuminate\Support\Facades\DB::commit();
            return back()->with('success', 'Data Cabang berhasil diperbarui!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage())->withInput();
        }
    }

    public function toggleActive($id)
    {
        $cabang = Cabang::findOrFail($id);
        $cabang->update(['is_active' => !$cabang->is_active]);
        
        $status = $cabang->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', 'Cabang ' . $cabang->nama_cabang . ' berhasil ' . $status . '!');
    }
}
