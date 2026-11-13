<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RequestOperasional;
use App\Models\PusatAplikasiLayanan;
use App\Models\MutasiStok;
use App\Models\MutasiCash;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminRequestOperasionalController extends Controller
{
    public function index()
    {
        $requests = RequestOperasional::with([
            'kasir', 'cabang', 'detail.barangFisik', 'transaksi', 'admin', 'masterAplikasiLayanan'
        ])->get()->map(function($item) {
            // Normalize status for sorting: 1 = pending, 2 = waiting kasir, 3 = selesai/ditolak
            $sortStatus = 3;
            if ($item->status_request === 'menunggu_admin') $sortStatus = 1;
            elseif ($item->status_request === 'menunggu_konfirmasi_kasir') $sortStatus = 2;

            $item->is_bantuan = false;
            $item->sort_status = $sortStatus;
            $item->sort_date = $item->created_at->timestamp;
            $item->normalized_status = $item->status_request;
            return $item;
        });

        $bantuanPusat = \App\Models\TransaksiBantuanPusat::with(['kasir', 'cabang', 'barang'])->get()->map(function($item) {
            $sortStatus = 3;
            if ($item->status === 'menunggu_pusat') $sortStatus = 1;
            elseif ($item->status === 'diproses_admin') $sortStatus = 2;

            $item->is_bantuan = true;
            $item->sort_status = $sortStatus;
            $item->sort_date = $item->created_at->timestamp;
            $item->normalized_status = $item->status;
            return $item;
        });

        $allItems = $requests->concat($bantuanPusat)->sort(function($a, $b) {
            if ($a->sort_status === $b->sort_status) {
                return $b->sort_date <=> $a->sort_date; // Latest first
            }
            return $a->sort_status <=> $b->sort_status; // 1 (pending) top, 3 (selesai) bottom
        })->values();

        $dompetPusat = PusatAplikasiLayanan::with('masterAplikasiLayanan')->get();
        $cabangs = \App\Models\Cabang::all();

        return view('admin.request_operasional.index', compact('allItems', 'dompetPusat', 'cabangs'));
    }

    public function tolakRequest(Request $request, $id)
    {
        $reqOp = RequestOperasional::findOrFail($id);
        $reqOp->update([
            'status_request' => 'ditolak',
            'status'         => 'selesai',
            'admin_id'       => Auth::id()
        ]);

        return back()->with('success', 'Request operasional berhasil ditolak.');
    }

    public function prosesIsiSaldo(Request $request, $id)
    {
        $reqOp = RequestOperasional::findOrFail($id);
        
        $request->validate([
            'admin_dompet_id' => 'required|exists:pusat_aplikasi_layanan,id',
            'biaya_admin_modal' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $dompetAdmin = PusatAplikasiLayanan::findOrFail($request->admin_dompet_id);
            $totalKeluar = $reqOp->nominal_request + $request->biaya_admin_modal;

            if ($dompetAdmin->saldo_sekarang < $totalKeluar) {
                return back()->with('error', 'Saldo Dompet Pusat tidak mencukupi.');
            }

            // Kurangi saldo admin
            $dompetAdmin->saldo_sekarang -= $totalKeluar;
            $dompetAdmin->save();

            // Update Request
            $reqOp->admin_dompet_id = $request->admin_dompet_id;
            $reqOp->biaya_admin_modal = $request->biaya_admin_modal;
            $reqOp->status_request = 'menunggu_konfirmasi_kasir';
            $reqOp->status = 'disetujui';
            $reqOp->admin_id = Auth::id();
            $reqOp->save();

            DB::commit();
            return back()->with('success', 'Isi Saldo Cabang berhasil diproses. Menunggu konfirmasi Kasir.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses isi saldo: ' . $e->getMessage());
        }
    }

    public function prosesBantuanTransaksi(Request $request, $id)
    {
        $bantuan = \App\Models\TransaksiBantuanPusat::findOrFail($id);

        $request->validate([
            'admin_dompet_id' => 'required|exists:pusat_aplikasi_layanan,id',
            'biaya_admin_modal' => 'required|numeric|min:0',
            'bukti_foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $dompetAdmin = PusatAplikasiLayanan::findOrFail($request->admin_dompet_id);
            $totalKeluar = $request->biaya_admin_modal;

            // Hitung fee cabang berdasarkan modal asli dari admin pusat
            $produk = $bantuan->barang;
            $digitalBService = app(\App\Services\DigitalBService::class);
            $validasi = $digitalBService->validasiDanCariFee($produk->id, (float) $request->biaya_admin_modal);
            $fee = $validasi['success'] ? $validasi['fee'] : 0;
            $totalBayar = (float) $request->biaya_admin_modal + $fee;

            if ($produk->jenis_transaksi === 'cash_in') {
                if ($dompetAdmin->saldo_sekarang < $totalKeluar) {
                    return back()->with('error', 'Saldo Dompet Pusat tidak mencukupi untuk memotong modal asli.');
                }
                // Kurangi saldo admin (modal)
                $dompetAdmin->saldo_sekarang -= $totalKeluar;
            } else {
                // Untuk cash_out (Tarik Tunai), pelanggan transfer (modal + fee) ke dompet admin
                $dompetAdmin->saldo_sekarang += $totalBayar;
            }
            $dompetAdmin->save();

            $pathBukti = null;
            if ($request->hasFile('bukti_foto')) {
                $pathBukti = $request->file('bukti_foto')->store('bantuan_pusat', 'public');
            }

            // Update TransaksiBantuanPusat
            $bantuan->update([
                'pusat_aplikasi_layanan_id' => $dompetAdmin->id,
                'harga_modal_pusat' => $request->biaya_admin_modal,
                'fee_cabang' => $fee,
                'total_bayar_pelanggan' => $totalBayar,
                'bukti_gambar' => $pathBukti,
                'status' => 'diproses_admin',
                'admin_id' => Auth::id()
            ]);

            DB::commit();
            return back()->with('success', 'Request Bantuan berhasil diproses. Menunggu konfirmasi Kasir.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses bantuan transaksi: ' . $e->getMessage());
        }
    }

    public function tolakBantuanTransaksi(Request $request, $id)
    {
        $bantuan = \App\Models\TransaksiBantuanPusat::findOrFail($id);
        $bantuan->update([
            'status' => 'ditolak',
            'admin_id' => Auth::id()
        ]);

        return back()->with('success', 'Request bantuan berhasil ditolak.');
    }

    public function prosesRestokBarang(Request $request, $id)
    {
        $reqOp = RequestOperasional::findOrFail($id);

        $request->validate([
            'asal_type' => 'required|in:pusat,cabang',
            'asal_cabang_id' => 'required_if:asal_type,cabang|exists:cabang,id',
        ]);

        DB::beginTransaction();
        try {
            // Kita bikin record Mutasi Stok
            $mutasi = new MutasiStok();
            $mutasi->cabang_tujuan_id = $reqOp->cabang_id;
            $mutasi->status = 'dikirim';
            if ($request->asal_type === 'cabang') {
                $mutasi->asal_cabang_id = $request->asal_cabang_id;
            }
            $mutasi->save();

            foreach ($reqOp->detail as $dtl) {
                if ($dtl->barang_fisik_id) {
                    \App\Models\DetailMutasiStok::create([
                        'mutasi_stok_id' => $mutasi->id,
                        'barang_id' => $dtl->barang_fisik_id,
                        'kuantitas' => $dtl->nominal_atau_qty,
                    ]);

                    // Kurangi stok asal jika dari cabang
                    if ($request->asal_type === 'cabang') {
                        $stokAsal = \App\Models\StokCabang::where('cabang_id', $request->asal_cabang_id)
                            ->where('barang_id', $dtl->barang_fisik_id)->first();
                        if ($stokAsal) {
                            $stokAsal->jumlah_stok -= $dtl->nominal_atau_qty;
                            $stokAsal->save();
                        }
                    }
                }
            }

            $reqOp->status_request = 'menunggu_konfirmasi_kasir';
            $reqOp->status = 'disetujui';
            $reqOp->admin_id = Auth::id();
            $reqOp->mutasi_id = $mutasi->id;
            $reqOp->save();

            DB::commit();
            return back()->with('success', 'Mutasi stok barang dalam pengiriman.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses mutasi stok: ' . $e->getMessage());
        }
    }

    public function prosesRestokCash(Request $request, $id)
    {
        $reqOp = RequestOperasional::findOrFail($id);

        $request->validate([
            'asal_type' => 'required|in:pusat,cabang',
            'asal_cabang_id' => 'required_if:asal_type,cabang|exists:cabang,id',
        ]);

        DB::beginTransaction();
        try {
            // Kita bikin record Mutasi Cash
            $mutasi = new MutasiCash();
            $mutasi->asal_type = $request->asal_type;
            if ($request->asal_type === 'cabang') {
                $mutasi->asal_cabang_id = $request->asal_cabang_id;
            }
            $mutasi->tujuan_cabang_id = $reqOp->cabang_id;
            $mutasi->nominal = $reqOp->nominal_request;
            $mutasi->status = 'dalam_pengiriman';
            $mutasi->admin_id = Auth::id();
            $mutasi->save();

            $reqOp->status_request = 'menunggu_konfirmasi_kasir';
            $reqOp->status = 'disetujui';
            $reqOp->admin_id = Auth::id();
            $reqOp->mutasi_id = $mutasi->id;
            $reqOp->save();

            DB::commit();
            return back()->with('success', 'Mutasi uang cash fisik dalam pengiriman.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses mutasi cash: ' . $e->getMessage());
        }
    }

    public function exportPdf(Request $request)
    {
        $cabangId = $request->query('cabang');
        $status = $request->query('status');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Query RequestOperasional
        $queryReq = RequestOperasional::with(['kasir', 'cabang', 'detail.barangFisik', 'transaksi', 'admin', 'masterAplikasiLayanan']);
        if ($cabangId) $queryReq->where('cabang_id', $cabangId);
        if ($startDate) $queryReq->whereDate('created_at', '>=', $startDate);
        if ($endDate) $queryReq->whereDate('created_at', '<=', $endDate);
        
        $requests = $queryReq->get()->map(function($item) {
            $sortStatus = 3;
            if ($item->status_request === 'menunggu_admin') $sortStatus = 1;
            elseif ($item->status_request === 'menunggu_konfirmasi_kasir') $sortStatus = 2;

            $item->is_bantuan = false;
            $item->sort_status = $sortStatus;
            $item->sort_date = $item->created_at->timestamp;
            $item->normalized_status = $item->status_request;
            return $item;
        });

        // Query TransaksiBantuanPusat
        $queryBan = \App\Models\TransaksiBantuanPusat::with(['kasir', 'cabang', 'barang']);
        if ($cabangId) $queryBan->where('cabang_id', $cabangId);
        if ($startDate) $queryBan->whereDate('created_at', '>=', $startDate);
        if ($endDate) $queryBan->whereDate('created_at', '<=', $endDate);
        
        $bantuanPusat = $queryBan->get()->map(function($item) {
            $sortStatus = 3;
            if ($item->status === 'menunggu_pusat') $sortStatus = 1;
            elseif ($item->status === 'diproses_admin') $sortStatus = 2;

            $item->is_bantuan = true;
            $item->sort_status = $sortStatus;
            $item->sort_date = $item->created_at->timestamp;
            $item->normalized_status = $item->status;
            return $item;
        });

        // Combine
        $allItems = $requests->concat($bantuanPusat)->sort(function($a, $b) {
            if ($a->sort_status === $b->sort_status) {
                return $b->sort_date <=> $a->sort_date;
            }
            return $a->sort_status <=> $b->sort_status;
        })->values();

        // Apply status filter
        if ($status) {
            $allItems = $allItems->filter(function($item) use ($status) {
                $isPending = ($item->normalized_status === 'menunggu_admin' || $item->normalized_status === 'menunggu_pusat');
                $isWaitingKasir = ($item->normalized_status === 'menunggu_konfirmasi_kasir' || $item->normalized_status === 'diproses_admin');
                
                $statusType = $isPending ? 'pending' : ($isWaitingKasir ? 'waiting_kasir' : 'completed');
                return $statusType === $status;
            })->values();
        }

        $cabang = $cabangId ? \App\Models\Cabang::find($cabangId) : null;

        return view('admin.request_operasional.pdf', compact('allItems', 'cabang', 'status', 'startDate', 'endDate'));
    }
}
