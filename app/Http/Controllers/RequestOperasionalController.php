<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RequestOperasional;
use App\Models\DetailRequestOperasional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestOperasionalController extends Controller
{
    /**
     * Tampilkan halaman form request (opsional, jika ada frontend).
     * Disini kita fokus ke fungsional API/Store.
     */
    public function index(Request $request)
    {
        $query = RequestOperasional::with([
            'detail', 
            'admin', 
            'transaksi',
            'masterAplikasiLayanan'
        ])->where('cabang_id', Auth::user()->cabang_id);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }
        if ($request->filled('jenis_request')) {
            $query->where('jenis_request', $request->jenis_request);
        }

        $requests = collect();
        
        if (!$request->filled('jenis_request') || $request->jenis_request != 'bantuan_digital_b') {
            $requests = $requests->merge($query->latest()->get());
        }
        
        // Fetch TransaksiBantuanPusat
        $bantuanQuery = \App\Models\TransaksiBantuanPusat::with(['barang', 'apkLayananPusat.masterAplikasiLayanan'])
            ->where('cabang_id', Auth::user()->cabang_id);
            
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $bantuanQuery->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }
        
        if (!$request->filled('jenis_request') || $request->jenis_request == 'bantuan_digital_b') {
            $requests = $requests->merge($bantuanQuery->latest()->get());
        }
        
        // Sort combined requests by created_at descending
        $requests = $requests->sortByDesc('created_at')->values();
        
        $cabangLayananIds = \App\Models\CabangAplikasiLayanan::where('cabang_id', Auth::user()->cabang_id)
            ->pluck('master_aplikasi_layanan_id');
        $masterLayanan = \App\Models\MasterAplikasiLayanan::whereIn('id', $cabangLayananIds)->get();
        
        $barangFisik = \App\Models\MasterBarang::whereIn('tipe_barang', ['Fisik ACC', 'Fisik Voucher Kartu'])->get();
        $barangDigitalB = \App\Models\MasterBarang::where('tipe_barang', 'Digital B')->where('jenis_transaksi', 'cash_in')->get();

        return view('kasir.request_operasional.index', compact('requests', 'masterLayanan', 'barangFisik', 'barangDigitalB'));
    }

    public function exportPdf(Request $request)
    {
        $query = RequestOperasional::with([
            'detail.barangFisik', 
            'admin', 
            'transaksi',
            'masterAplikasiLayanan'
        ])->where('cabang_id', Auth::user()->cabang_id);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }
        if ($request->filled('jenis_request')) {
            $query->where('jenis_request', $request->jenis_request);
        }

        $requests = $query->latest()->get();
        
        $pdf = \PDF::loadView('kasir.request_operasional.pdf', compact('requests', 'request'));
        return $pdf->stream('Laporan_Request_Operasional.pdf');
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_request' => 'required|in:isi_saldo,bantuan_digital_b,restok_barang,restok_cash',
            'keterangan'    => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $reqOp = new RequestOperasional();
            $reqOp->kasir_id = Auth::id();
            $reqOp->cabang_id = Auth::user()->cabang_id;
            $reqOp->jenis_request = $request->jenis_request;
            $reqOp->status_request = 'menunggu_admin';
            $reqOp->keterangan = $request->keterangan;

            if ($request->jenis_request === 'isi_saldo') {
                $request->validate([
                    'master_aplikasi_layanan_id' => 'required|exists:master_aplikasi_layanan,id',
                    'nominal_request' => 'required|numeric|min:1',
                ]);
                $reqOp->master_aplikasi_layanan_id = $request->master_aplikasi_layanan_id;
                $reqOp->nominal_request = $request->nominal_request;
            } 
            elseif ($request->jenis_request === 'bantuan_digital_b') {
                $request->validate([
                    'master_aplikasi_layanan_id' => 'required|exists:master_aplikasi_layanan,id',
                    'nominal_request' => 'required|numeric|min:1',
                    'tujuan_rekening' => 'required|string',
                ]);
                $reqOp->master_aplikasi_layanan_id = $request->master_aplikasi_layanan_id;
                $reqOp->nominal_request = $request->nominal_request;
                $reqOp->tujuan_rekening = $request->tujuan_rekening;
            }
            elseif ($request->jenis_request === 'restok_cash') {
                $request->validate([
                    'nominal_request' => 'required|numeric|min:1',
                ]);
                $reqOp->nominal_request = $request->nominal_request;
            }

            $reqOp->save();

            // Khusus restok_barang kita insert ke detail
            if ($request->jenis_request === 'restok_barang') {
                $request->validate([
                    'items' => 'required|array',
                    'items.*.barang_fisik_id' => 'required|exists:master_barang,id',
                    'items.*.qty' => 'required|integer|min:1',
                ]);

                foreach ($request->items as $item) {
                    DetailRequestOperasional::create([
                        'request_id' => $reqOp->id,
                        'barang_fisik_id' => $item['barang_fisik_id'],
                        'nominal_atau_qty' => $item['qty'],
                    ]);
                }
            }

            DB::commit();
            return back()->with('success', 'Request operasional berhasil dikirim ke Pusat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengirim request: ' . $e->getMessage());
        }
    }

    /**
     * Kasir mengonfirmasi penerimaan request operasional yang dikirim admin.
     */
    public function konfirmasiKasir($id)
    {
        $reqOp = RequestOperasional::where('cabang_id', Auth::user()->cabang_id)
            ->findOrFail($id);

        if ($reqOp->status_request !== 'menunggu_konfirmasi_kasir') {
            return back()->with('error', 'Status request tidak valid untuk dikonfirmasi.');
        }

        DB::beginTransaction();
        try {
            if ($reqOp->jenis_request === 'isi_saldo') {
                // Tambah saldo layanan cabang (menggunakan skema baru CabangAplikasiLayanan)
                $saldoLayanan = \App\Models\CabangAplikasiLayanan::firstOrCreate(
                    [
                        'cabang_id' => $reqOp->cabang_id, 
                        'master_aplikasi_layanan_id' => $reqOp->master_aplikasi_layanan_id
                    ],
                    [
                        'saldo_awal' => 0,
                        'saldo_sekarang' => 0
                    ]
                );
                $saldoLayanan->saldo_sekarang += $reqOp->nominal_request;
                $saldoLayanan->save();
            } 
            elseif ($reqOp->jenis_request === 'bantuan_digital_b') {
                // Digital B cash in besar
                // Uang cash bertambah di laci kasir (diwakili oleh total_biaya_pelanggan)
                // Kita catat total_biaya_pelanggan sebagai Transaksi
                // Hal ini biasanya sudah di-generate transaksinya oleh admin, kita tinggal ubah status
                if ($reqOp->transaksi) {
                    $reqOp->transaksi->status = 'selesai';
                    $reqOp->transaksi->save();
                }
            }
            elseif ($reqOp->jenis_request === 'restok_cash') {
                if ($reqOp->mutasi_id) {
                    $mutasi = \App\Models\MutasiUang::find($reqOp->mutasi_id);
                    if ($mutasi && $mutasi->status !== 'diterima') {
                        throw new \Exception('Silakan konfirmasi penerimaan uang fisik melalui menu Penerimaan Mutasi terlebih dahulu.');
                    }
                }
            }
            elseif ($reqOp->jenis_request === 'restok_barang') {
                if ($reqOp->mutasi_id) {
                    $mutasi = \App\Models\MutasiStok::find($reqOp->mutasi_id);
                    if ($mutasi && $mutasi->status !== 'diterima') {
                        throw new \Exception('Silakan konfirmasi penerimaan fisik barang melalui menu Penerimaan Mutasi terlebih dahulu.');
                    }
                }
            }

            $reqOp->status_request = 'selesai';
            $reqOp->save();

            DB::commit();
            return back()->with('success', 'Penerimaan berhasil dikonfirmasi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengonfirmasi penerimaan: ' . $e->getMessage());
        }
    }

    public function batalkanRequest($id)
    {
        $reqOp = RequestOperasional::where('id', $id)->where('cabang_id', Auth::user()->cabang_id)->firstOrFail();
        
        if ($reqOp->status_request !== 'menunggu_admin') {
            return back()->with('error', 'Request tidak dapat dibatalkan karena sudah diproses atau selesai.');
        }

        DB::beginTransaction();
        try {
            // Hapus detail request terlebih dahulu
            \App\Models\DetailRequestOperasional::where('request_operasional_id', $reqOp->id)->delete();
            $reqOp->delete();
            
            DB::commit();
            return back()->with('success', 'Request berhasil dibatalkan dan dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membatalkan request: ' . $e->getMessage());
        }
    }
}
