<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MutasiService;

class MutasiCabangController extends Controller
{
    protected $mutasiService;

    public function __construct(MutasiService $mutasiService)
    {
        $this->mutasiService = $mutasiService;
    }

    /**
     * Cabang mengonfirmasi penerimaan mutasi Uang Cash.
     */
    public function terimaMutasiCash(Request $request, $id)
    {
        try {
            $this->mutasiService->terimaCashFisik($id);
            return back()->with('success', 'Mutasi uang cash berhasil diterima. Saldo kas cabang bertambah.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cabang mengonfirmasi penerimaan mutasi Barang Fisik.
     */
    public function terimaMutasiBarang(Request $request, $id)
    {
        try {
            $this->mutasiService->terimaBarangFisik($id);
            return back()->with('success', 'Mutasi stok barang berhasil diterima. Stok fisik cabang bertambah.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
