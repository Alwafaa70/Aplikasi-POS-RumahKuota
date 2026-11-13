<?php

namespace App\Http\Controllers;

use App\Models\MasterAplikasiLayanan;
use Illuminate\Http\Request;

class MasterAplikasiLayananController extends Controller
{
    public function index()
    {
        $layanans = MasterAplikasiLayanan::orderBy('nama_layanan')->get();
        return view('master_aplikasi_layanan.index', compact('layanans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_layanan'   => 'required|string|max:255|unique:master_aplikasi_layanan,nama_layanan',
            'jenis_layanan'  => 'required|string|max:50',
        ], [
            'nama_layanan.unique' => 'Nama Layanan/Bank ini sudah terdaftar sebelumnya.'
        ]);

        MasterAplikasiLayanan::create([
            'nama_layanan'   => $request->nama_layanan,
            'jenis_layanan'  => $request->jenis_layanan,
        ]);

        return redirect()->route('master.aplikasi_layanan.index')->with('success', 'Aplikasi Layanan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_layanan'   => 'required|string|max:255|unique:master_aplikasi_layanan,nama_layanan,' . $id,
            'jenis_layanan'  => 'required|string|max:50',
        ], [
            'nama_layanan.unique' => 'Nama Layanan/Bank ini sudah terdaftar sebelumnya.'
        ]);

        $layanan = MasterAplikasiLayanan::findOrFail($id);

        $layanan->update([
            'nama_layanan'   => $request->nama_layanan,
            'jenis_layanan'  => $request->jenis_layanan,
        ]);

        return redirect()->route('master.aplikasi_layanan.index')->with('success', 'Aplikasi Layanan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $layanan = MasterAplikasiLayanan::findOrFail($id);
        
        // Pastikan tidak dihapus jika sedang dipakai
        if ($layanan->cabangAplikasiLayanan()->exists() || $layanan->pusatAplikasiLayanan()->exists()) {
            return redirect()->route('master.aplikasi_layanan.index')->with('error', 'Gagal menghapus! Layanan sedang digunakan oleh Cabang atau Pusat.');
        }

        $layanan->delete();
        return redirect()->route('master.aplikasi_layanan.index')->with('success', 'Aplikasi Layanan berhasil dihapus.');
    }
}
