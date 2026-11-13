<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RequestOperasional;
use App\Services\MutasiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminFulfillmentController extends Controller
{
    protected $mutasiService;

    public function __construct(MutasiService $mutasiService)
    {
        $this->mutasiService = $mutasiService;
    }

    /**
     * Tampilkan daftar request dari cabang.
     */
    public function index()
    {
        $requests = RequestOperasional::with('detail', 'cabang', 'kasir')
                        ->where('status', 'menunggu')
                        ->latest()
                        ->get();
        
        return view('admin.fulfillment.index', compact('requests'));
    }

    /**
     * Setujui request saldo digital.
     * Langsung transfer saldo digital dari Pusat ke Cabang.
     */
    public function approveDigital(Request $request, $id)
    {
        $request->validate([
            'apk_sumber_id' => 'required|exists:apk_layanan,id',
            'fee_admin'     => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $reqOp = RequestOperasional::with('detail')->lockForUpdate()->findOrFail($id);

            if ($reqOp->status !== 'menunggu' || $reqOp->jenis_request !== 'saldo_digital') {
                throw new \Exception("Request tidak valid untuk tipe saldo digital.");
            }

            // Ambil detail pertama (asumsi 1 request digital = 1 detail)
            $detail = $reqOp->detail->first();
            $nominal = $detail->nominal_atau_qty;
            $apkTujuanId = $detail->apk_layanan_id;

            // Lakukan mutasi digital instan via MutasiService
            $this->mutasiService->transferSaldoDigital(
                Auth::id(),
                $reqOp->cabang_id,
                $request->apk_sumber_id,
                $apkTujuanId,
                $nominal,
                $request->fee_admin,
                "Pemenuhan request operasional #{$reqOp->id}"
            );

            $reqOp->update(['status' => 'disetujui']);

            DB::commit();
            return back()->with('success', 'Request Saldo Digital disetujui. Saldo berhasil dimutasi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Setujui request cash operasional.
     * Menginisiasi mutasi Cash dari Pusat ke Cabang.
     */
    public function approveCash(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $reqOp = RequestOperasional::with('detail')->lockForUpdate()->findOrFail($id);

            if ($reqOp->status !== 'menunggu' || $reqOp->jenis_request !== 'cash_operasional') {
                throw new \Exception("Request tidak valid untuk tipe cash operasional.");
            }

            $detail = $reqOp->detail->first();
            $nominal = $detail->nominal_atau_qty;

            // Inisiasi pengiriman Cash Fisik dari Pusat (cabangAsalId = null) ke Cabang Tujuan
            $this->mutasiService->kirimCashFisik(
                Auth::id(),
                null,
                $reqOp->cabang_id,
                $nominal,
                "Pemenuhan request operasional #{$reqOp->id}"
            );

            $reqOp->update(['status' => 'disetujui']);

            DB::commit();
            return back()->with('success', 'Request Cash disetujui. Mutasi uang cash sedang dikirim.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Setujui request barang fisik.
     * Menginisiasi mutasi Stok dari Pusat ke Cabang.
     */
    public function approveBarang(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $reqOp = RequestOperasional::with('detail')->lockForUpdate()->findOrFail($id);

            if ($reqOp->status !== 'menunggu' || $reqOp->jenis_request !== 'barang_fisik') {
                throw new \Exception("Request tidak valid untuk tipe barang fisik.");
            }

            $items = [];
            foreach ($reqOp->detail as $det) {
                $items[$det->barang_fisik_id] = $det->nominal_atau_qty;
            }

            // Inisiasi pengiriman Barang Fisik dari Pusat (cabangAsalId = null) ke Cabang Tujuan
            $this->mutasiService->kirimBarangFisik(
                Auth::id(),
                null,
                $reqOp->cabang_id,
                $items
            );

            $reqOp->update(['status' => 'disetujui']);

            DB::commit();
            return back()->with('success', 'Request Barang disetujui. Mutasi stok sedang dikirim.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Tolak request.
     */
    public function reject(Request $request, $id)
    {
        $reqOp = RequestOperasional::findOrFail($id);
        $reqOp->update(['status' => 'ditolak']);
        return back()->with('success', 'Request telah ditolak.');
    }
}
