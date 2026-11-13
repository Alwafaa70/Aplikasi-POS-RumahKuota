<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReturnService;

class ReturController extends Controller
{
    protected $returnService;

    public function __construct(ReturnService $returnService)
    {
        $this->returnService = $returnService;
    }

    /**
     * Mengajukan retur barang rusak.
     */
    public function ajukan(Request $request, $pembelianId)
    {
        $request->validate([
            'items'       => 'required|array|min:1',
            'items.*.qty' => 'required|integer|min:0',
        ]);

        // Filter items yang qty > 0
        $itemsToReturn = [];
        foreach ($request->items as $barangId => $data) {
            if ($data['qty'] > 0) {
                $itemsToReturn[$barangId] = (int) $data['qty'];
            }
        }

        if (empty($itemsToReturn)) {
            return back()->with('error', 'Tidak ada barang yang dipilih untuk diretur.');
        }

        try {
            $retur = $this->returnService->ajukanRetur($pembelianId, $itemsToReturn);
            return back()->with('success', 'Retur berhasil diajukan dengan status menunggu solusi.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Menyelesaikan retur (Pilih Kirim Ulang atau Refund)
     */
    public function selesaikan(Request $request, $id)
    {
        $request->validate([
            'solusi' => 'required|in:kirim_ulang,refund',
        ]);

        try {
            $this->returnService->selesaikanRetur($id, $request->solusi);
            
            $pesan = $request->solusi === 'kirim_ulang' 
                        ? 'Retur diselesaikan: Barang pengganti telah diterima (Stok bertambah).'
                        : 'Retur diselesaikan: Dana telah direfund ke Kas/Saldo Pusat.';

            return back()->with('success', $pesan);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
