<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MutasiStok;
use App\Models\MutasiUang;
use App\Services\MutasiService;

class KasirPenerimaanController extends Controller
{
    protected MutasiService $mutasiService;

    public function __construct(MutasiService $mutasiService)
    {
        $this->mutasiService = $mutasiService;
    }

    public function index()
    {
        $user = Auth::user();
        $cabangId = $user->cabang_id;

        $mutasiBarangPending = MutasiStok::with(['cabangAsal', 'detailMutasi.barang'])
            ->where('cabang_tujuan_id', $cabangId)
            ->whereIn('status', ['dikirim', 'dalam_perjalanan'])
            ->get();

        $mutasiCashPending = MutasiUang::with(['cabangAsal'])
            ->where('cabang_tujuan_id', $cabangId)
            ->where('jenis_mutasi', 'cash')
            ->whereIn('status', ['dikirim', 'dalam_perjalanan'])
            ->get();

        $mutasiBarangAsalPending = MutasiStok::with(['cabangTujuan', 'detailMutasi.barang'])
            ->where('cabang_asal_id', $cabangId)
            ->where('status', 'menunggu_konfirmasi_asal')
            ->get();

        $mutasiCashAsalPending = MutasiUang::with(['cabangTujuan'])
            ->where('cabang_asal_id', $cabangId)
            ->where('jenis_mutasi', 'cash')
            ->where('status', 'menunggu_konfirmasi_asal')
            ->get();

        return view('kasir.penerimaan.index', compact('mutasiBarangPending', 'mutasiCashPending', 'mutasiBarangAsalPending', 'mutasiCashAsalPending'));
    }

    public function terimaBarangView($id)
    {
        $user = Auth::user();
        $cabangId = $user->cabang_id;

        $mutasi = MutasiStok::with(['cabangAsal', 'detailMutasi.barang'])
            ->where('id', $id)
            ->where('cabang_tujuan_id', $cabangId)
            ->whereIn('status', ['dikirim', 'dalam_perjalanan'])
            ->firstOrFail();

        return view('kasir.penerimaan.terima_barang', compact('mutasi'));
    }

    public function terimaBarangProcess(Request $request, $id)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.barang_id' => 'required|exists:master_barang,id',
            'items.*.baik' => 'required|integer|min:0',
            'items.*.rusak' => 'required|integer|min:0',
            'items.*.hilang' => 'required|integer|min:0',
            'items.*.catatan' => 'nullable|string',
            'catatan_penerimaan' => 'nullable|string',
            'bukti_gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        try {
            $buktiPath = null;
            if ($request->hasFile('bukti_gambar')) {
                $buktiPath = $request->file('bukti_gambar')->store('mutasi_bukti', 'public');
            }

            $this->mutasiService->terimaBarangBersyarat($id, $request->items, $request->catatan_penerimaan, $buktiPath, Auth::id());
            return redirect()->route('kasir.penerimaan.index')->with('success', 'Mutasi barang berhasil diproses dan dikonfirmasi.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function terimaCashProcess($id)
    {
        try {
            $this->mutasiService->terimaCashFisik($id, Auth::id());
            return redirect()->route('kasir.penerimaan.index')->with('success', 'Mutasi uang cash berhasil diterima. Saldo kas cabang bertambah.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function konfirmasiAsalBarang(Request $request, $id)
    {
        $request->validate([
            'keterangan' => 'nullable|string',
            'bukti_gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        try {
            $buktiPath = null;
            if ($request->hasFile('bukti_gambar')) {
                $buktiPath = $request->file('bukti_gambar')->store('mutasi_bukti_asal', 'public');
            }

            $this->mutasiService->konfirmasiAsalBarang($id, Auth::id(), $request->keterangan, $buktiPath);
            return back()->with('success', 'Berhasil mengonfirmasi pengiriman mutasi stok.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function tolakAsalBarang(Request $request, $id)
    {
        $request->validate([
            'alasan' => 'required|string'
        ]);

        try {
            $this->mutasiService->tolakAsalBarang($id, Auth::id(), $request->alasan);
            return back()->with('success', 'Mutasi stok berhasil ditolak.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function konfirmasiAsalCash(Request $request, $id)
    {
        $request->validate([
            'keterangan' => 'nullable|string',
            'bukti_gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        try {
            $buktiPath = null;
            if ($request->hasFile('bukti_gambar')) {
                $buktiPath = $request->file('bukti_gambar')->store('mutasi_bukti_asal', 'public');
            }

            $this->mutasiService->konfirmasiAsalCash($id, Auth::id(), $request->keterangan, $buktiPath);
            return back()->with('success', 'Berhasil mengonfirmasi pengiriman mutasi uang fisik.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function tolakAsalCash(Request $request, $id)
    {
        $request->validate([
            'alasan' => 'required|string'
        ]);

        try {
            $this->mutasiService->tolakAsalCash($id, Auth::id(), $request->alasan);
            return back()->with('success', 'Mutasi uang fisik berhasil ditolak.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
