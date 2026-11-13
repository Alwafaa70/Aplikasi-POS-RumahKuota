<?php

namespace App\Http\Controllers;

use App\Models\TransaksiBantuanPusat;
use App\Models\PusatAplikasiLayanan;
use App\Models\MasterBarang;
use App\Services\DigitalBService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminBantuanPusatController extends Controller
{
    protected DigitalBService $digitalBService;

    public function __construct(DigitalBService $digitalBService)
    {
        $this->digitalBService = $digitalBService;
    }

    /**
     * Admin lihat daftar request bantuan pusat.
     */
    public function index()
    {
        $requests = TransaksiBantuanPusat::with(['kasir', 'cabang', 'barang'])
            ->orderByRaw("CASE WHEN status = 'menunggu_pusat' THEN 0 WHEN status = 'diproses_admin' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at')
            ->get();

        $apkLayananPusat = PusatAplikasiLayanan::with('masterAplikasiLayanan')->get();

        return view('admin.bantuan_pusat.index', compact('requests', 'apkLayananPusat'));
    }

    /**
     * Admin proses request bantuan (eksekusi TF/topup).
     */
    public function proses(Request $request, $id)
    {
        $request->validate([
            'apk_layanan_pusat_id' => 'required|exists:pusat_aplikasi_layanan,id',
            'harga_modal_pusat' => 'required|numeric|min:1',
            'bukti_gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $bantuan = TransaksiBantuanPusat::where('id', $id)
                ->where('status', 'menunggu_pusat')
                ->firstOrFail();

            $hargaModal = (float) $request->harga_modal_pusat;
            $apkPusatId = (int) $request->apk_layanan_pusat_id;

            // Hitung fee berdasarkan harga modal
            $validasi = $this->digitalBService->validasiDanCariFee($bantuan->barang_id, $hargaModal);
            $fee = $validasi['success'] ? $validasi['fee'] : $bantuan->fee_cabang;
            $totalBayar = $hargaModal + $fee;

            // Kurangi saldo pusat
            $pusatApk = PusatAplikasiLayanan::where('id', $apkPusatId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($pusatApk->saldo_sekarang < $hargaModal) {
                throw new \Exception('Saldo pusat ' . ($pusatApk->masterAplikasiLayanan->nama_layanan ?? 'Apk') . ' tidak mencukupi! Saldo: Rp ' . number_format($pusatApk->saldo_sekarang, 0, ',', '.'));
            }

            $pusatApk->decrement('saldo_sekarang', $hargaModal);

            // Upload bukti gambar
            $buktiPath = null;
            if ($request->hasFile('bukti_gambar')) {
                $buktiPath = $request->file('bukti_gambar')->store('bukti_bantuan', 'public');
            }

            // Update bantuan
            $bantuan->update([
                'admin_id' => Auth::id(),
                'harga_modal_pusat' => $hargaModal,
                'apk_layanan_pusat_id' => $apkPusatId,
                'fee_cabang' => $fee,
                'total_bayar_pelanggan' => $totalBayar,
                'bukti_gambar' => $buktiPath,
                'status' => 'diproses_admin',
            ]);

            DB::commit();
            return redirect()->route('admin.bantuan_pusat.index')
                ->with('success', 'Bantuan berhasil diproses! Menunggu konfirmasi kasir. Total bayar pelanggan: Rp ' . number_format($totalBayar, 0, ',', '.'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
