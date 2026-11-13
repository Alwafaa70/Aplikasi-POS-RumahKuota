<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cabang;
use App\Models\MasterBarang;
use App\Models\StokPusat;
use App\Models\MutasiStok;
use App\Models\DetailMutasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminGudangController extends Controller
{
    public function index(\App\Services\EwsService $ewsService)
    {
        // 1. Ambil seluruh data kritis EWS terpadu (Gudang Pusat & Seluruh Cabang)
        $allKritis = $ewsService->getAllKritisItems(30);
        $ewsAlerts = [];
        $totalBarangKritisGudang = 0;
        $cabangsWithKritis = [];

        foreach ($allKritis as $kritisItem) {
            $lokasiKey = $kritisItem['tipe_lokasi'] === 'Gudang Pusat' ? 'Gudang Pusat' : 'Cabang: ' . $kritisItem['lokasi_nama'];
            if (!isset($ewsAlerts[$lokasiKey])) {
                $ewsAlerts[$lokasiKey] = [];
            }
            $ewsAlerts[$lokasiKey][] = (object) $kritisItem;

            if ($kritisItem['tipe_lokasi'] === 'Gudang Pusat') {
                $totalBarangKritisGudang++;
            } else {
                if ($kritisItem['cabang_id']) {
                    $cabangsWithKritis[$kritisItem['cabang_id']] = true;
                }
            }
        }
        $totalCabangKritis = count($cabangsWithKritis);
        $totalBarangKritisCabang = count($allKritis) - $totalBarangKritisGudang;
        $totalEwsAll = count($allKritis);
        $totalUnitRestokDibutuhkan = array_sum(array_column($allKritis, 'rekomendasi_restok'));

        // 2. Data Cabang untuk monitoring aset fisik & overview
        $cabangs = Cabang::with(['kasCabang', 'saldoApkLayanan', 'stokCabang.barang'])->get();
        $cabangs->transform(function ($cabang) use ($allKritis) {
            $allStokItems = [];
            $cabang->total_fisik_value = 0;
            
            foreach ($cabang->stokCabang as $stok) {
                if ($stok->barang && in_array($stok->barang->tipe_barang, ['Fisik ACC', 'Fisik Voucher Kartu'])) {
                    $cabang->total_fisik_value += ($stok->jumlah_stok * $stok->barang->harga_jual);
                    $allStokItems[] = [
                        'nama_barang' => $stok->barang->nama_barang,
                        'jumlah_stok' => $stok->jumlah_stok
                    ];
                }
            }
            
            $branchKritis = array_values(array_filter($allKritis, fn($item) => $item['cabang_id'] == $cabang->id));
            $cabang->ews_count = count($branchKritis);
            $cabang->has_ews = $cabang->ews_count > 0;
            $cabang->ews_items = $branchKritis;

            usort($allStokItems, fn($a, $b) => strcmp($a['nama_barang'], $b['nama_barang']));
            $cabang->all_stok = $allStokItems;
            return $cabang;
        });

        // Sort: Branches with EWS first, then by name
        $cabangs = $cabangs->sortBy([
            ['has_ews', 'desc'],
            ['nama_cabang', 'asc']
        ])->values();

        $barangFisik = MasterBarang::whereIn('tipe_barang', ['Fisik ACC', 'Fisik Voucher Kartu'])->get();
        $stokPusat = StokPusat::with('barang')->get()->keyBy('barang_id');

        // 3. EWS untuk Gudang Pusat
        $gudangPusat_ews_items = array_values(array_filter($allKritis, fn($item) => $item['tipe_lokasi'] === 'Gudang Pusat'));
        $gudangPusat_ews_count = count($gudangPusat_ews_items);
        $gudangPusat_has_ews = $gudangPusat_ews_count > 0;

        // Hitung nilai aset fisik Gudang Pusat
        $stokPusatBatches = \App\Models\BarangMasuk::with('barang')->where('stok_sisa', '>', 0)->get();
        $gudangPusat_total_fisik_value = $stokPusatBatches->sum(function($b) {
            return $b->barang ? ($b->stok_sisa * $b->barang->harga_jual) : 0;
        });

        // 4. Notifikasi dan Counter Operasional
        $pendingRequestOperasional = \App\Models\RequestOperasional::where('status_request', 'menunggu_admin')->count();
        $pendingBantuan = \App\Models\TransaksiBantuanPusat::where('status', 'menunggu_pusat')->count();
        $totalPendingRequest = $pendingRequestOperasional + $pendingBantuan;

        $totalDraftMutasiStok = \App\Models\MutasiStok::where('status', 'draft')->count();
        $totalDraftMutasiCash = \App\Models\MutasiUang::where('status', 'draft')->count();
        $totalPendingClosing = \App\Models\LaporanClosing::where('status_konfirmasi', 'menunggu_konfirmasi')->count();
        $totalEwsAllCabang = $totalBarangKritisCabang;

        return view('dashboards.admin_pusat', compact(
            'cabangs', 'barangFisik', 'stokPusat', 
            'allKritis', 'ewsAlerts',
            'gudangPusat_has_ews', 'gudangPusat_ews_count', 'gudangPusat_total_fisik_value', 'gudangPusat_ews_items',
            'totalBarangKritisGudang', 'totalBarangKritisCabang', 'totalCabangKritis', 'totalEwsAll', 'totalUnitRestokDibutuhkan',
            'totalPendingRequest', 'totalDraftMutasiStok', 'totalDraftMutasiCash',
            'totalPendingClosing', 'totalEwsAllCabang'
        ));
    }

    public function storeMutasi(Request $request, \App\Services\MutasiService $mutasiService)
    {
        $request->validate([
            'request_id' => 'nullable|exists:request_operasional,id',
            'action' => 'required|in:draft,kirim',
            'mutasi_items' => 'required|string', // JSON string from frontend
        ]);

        $items = json_decode($request->mutasi_items, true);
        if (!$items || !is_array($items) || count($items) == 0) {
            return back()->with('error', 'Daftar barang mutasi kosong.');
        }

        $adminId = Auth::id();
        $isDraft = $request->action === 'draft';

        // Group by (asal, tujuan)
        $grouped = [];
        foreach ($items as $item) {
            if (empty($item['cabang_tujuan_id']) || empty($item['barang_id']) || empty($item['qty'])) continue;
            
            $asal = empty($item['cabang_asal_id']) ? 'pusat' : $item['cabang_asal_id'];
            $tujuan = $item['cabang_tujuan_id'];
            $key = $asal . '_' . $tujuan;
            
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'asal' => $asal === 'pusat' ? null : $asal,
                    'tujuan' => $tujuan,
                    'items' => []
                ];
            }
            // Sum qty if same barang_id in the same group
            if (!isset($grouped[$key]['items'][$item['barang_id']])) {
                $grouped[$key]['items'][$item['barang_id']] = 0;
            }
            $grouped[$key]['items'][$item['barang_id']] += (int)$item['qty'];
        }

        try {
            DB::beginTransaction();
            $firstMutasi = null;
            foreach ($grouped as $group) {
                if ($isDraft) {
                    // Create draft mutasi directly without MutasiService
                    $mutasi = \App\Models\MutasiStok::create([
                        'admin_id'         => $adminId,
                        'cabang_asal_id'   => $group['asal'],
                        'cabang_tujuan_id' => $group['tujuan'],
                        'status'           => 'draft',
                        'tanggal_mutasi'   => now()->toDateString(),
                    ]);
                    foreach ($group['items'] as $bId => $qty) {
                        if ($qty > 0) {
                            \App\Models\DetailMutasi::create([
                                'mutasi_id' => $mutasi->id,
                                'barang_id' => $bId,
                                'kuantitas' => $qty,
                            ]);
                        }
                    }
                    if (!$firstMutasi) $firstMutasi = $mutasi;
                } else {
                    // Send directly using MutasiService
                    $mutasi = $mutasiService->kirimBarangFisik(
                        $adminId,
                        $group['asal'],
                        $group['tujuan'],
                        $group['items']
                    );
                    if (!$firstMutasi) $firstMutasi = $mutasi;
                }
            }

            // Jika ada request_id, hubungkan mutasi ke request tersebut
            if ($request->request_id && $firstMutasi && !$isDraft) {
                $reqOp = \App\Models\RequestOperasional::find($request->request_id);
                if ($reqOp) {
                    $reqOp->update([
                        'status_request' => 'menunggu_konfirmasi_kasir',
                        'mutasi_id'      => $firstMutasi->id,
                        'admin_id'       => $adminId,
                    ]);
                    DB::commit();
                    return redirect()->route('admin.request_operasional.index')->with('success', 'Mutasi stok berhasil dikirim dan request operasional cabang telah disetujui!');
                }
            }

            DB::commit();
            $msg = $isDraft ? 'Draft mutasi berhasil disimpan!' : 'Mutasi stok berhasil dibuat dan dikirim!';
            return redirect()->route('distribusi.mutasi.index')->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function hapusDraft($id)
    {
        $mutasi = \App\Models\MutasiStok::findOrFail($id);
        if ($mutasi->status !== 'draft') {
            return back()->with('error', 'Hanya mutasi berstatus draft yang dapat dihapus.');
        }
        $mutasi->delete();
        return back()->with('success', 'Draft mutasi berhasil dihapus.');
    }

    public function prosesDraft($id, \App\Services\MutasiService $mutasiService)
    {
        $mutasi = \App\Models\MutasiStok::with('detailMutasi')->findOrFail($id);
        if ($mutasi->status !== 'draft') {
            return back()->with('error', 'Mutasi ini bukan draft.');
        }

        try {
            DB::beginTransaction();
            $items = [];
            foreach ($mutasi->detailMutasi as $dtl) {
                $items[$dtl->barang_id] = $dtl->kuantitas;
            }

            // Execute actual mutasi to deduct stock and generate detailed FIFO records
            $mutasiService->kirimBarangFisik(
                Auth::id(),
                $mutasi->cabang_asal_id,
                $mutasi->cabang_tujuan_id,
                $items
            );
            
            // Delete the draft mutasi as it is replaced by the real one from service
            $mutasi->delete();

            DB::commit();
            return back()->with('success', 'Draft mutasi berhasil diproses dan dikirim!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses draft: ' . $e->getMessage());
        }
    }
    public function printMutasi(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
        $endDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();

        $mutasiStok = \App\Models\MutasiStok::with(['cabangAsal', 'cabangTujuan', 'detailMutasi.barang', 'admin'])
                        ->where('status', '!=', 'draft')
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->orderBy('created_at', 'asc')
                        ->get();

        return view('distribusi_mutasi.print', compact('mutasiStok', 'startDate', 'endDate'));
    }

    public function storeMutasiCash(Request $request, \App\Services\MutasiService $mutasiService)
    {
        $request->validate([
            'asal_type' => 'required|in:pusat,cabang',
            'asal_cabang_id' => 'required_if:asal_type,cabang|nullable|exists:cabang,id',
            'tujuan_cabang_id' => 'required|exists:cabang,id',
            'nominal' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $adminId = Auth::id();
            $cabangAsalId = $request->asal_type === 'pusat' ? null : $request->asal_cabang_id;
            $isDraft = $request->action === 'draft';

            if ($isDraft) {
                $mutasi = \App\Models\MutasiUang::create([
                    'admin_id' => $adminId,
                    'cabang_asal_id' => $cabangAsalId,
                    'cabang_tujuan_id' => $request->tujuan_cabang_id,
                    'jenis_mutasi' => 'cash',
                    'nominal' => $request->nominal,
                    'keterangan' => $request->keterangan,
                    'status' => 'draft',
                    'tanggal_mutasi' => now()->toDateString(),
                ]);
            } else {
                $mutasi = $mutasiService->kirimCashFisik(
                    $adminId,
                    $cabangAsalId,
                    $request->tujuan_cabang_id,
                    $request->nominal,
                    $request->keterangan
                );
            }

            // Jika dari form Request Operasional
            if ($request->filled('request_id') && !$isDraft) {
                $reqOp = \App\Models\RequestOperasional::find($request->request_id);
                if ($reqOp) {
                    $reqOp->update([
                        'status_request' => 'menunggu_konfirmasi_kasir',
                        'admin_id' => $adminId,
                        'keterangan_admin' => 'Uang fisik telah diproses via mutasi: Rp ' . number_format($request->nominal, 0, ',', '.')
                    ]);
                }
            }

            DB::commit();
            
            if ($request->filled('request_id') && !$isDraft) {
                return redirect()->route('admin.request_operasional.index')->with('success', 'Request Restok Cash berhasil dieksekusi dan uang fisik diproses!');
            }
            
            $msg = $isDraft ? 'Draft mutasi uang fisik berhasil disimpan!' : 'Mutasi uang cash fisik berhasil dikirim!';
            return redirect()->route('distribusi.mutasi.index')->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengirim uang fisik: ' . $e->getMessage());
        }
    }
    public function hapusDraftCash($id)
    {
        $mutasi = \App\Models\MutasiUang::findOrFail($id);
        if ($mutasi->status !== 'draft') {
            return back()->with('error', 'Hanya mutasi berstatus draft yang dapat dihapus.');
        }
        $mutasi->delete();
        return back()->with('success', 'Draft mutasi uang fisik berhasil dihapus.');
    }

    public function prosesDraftCash($id, \App\Services\MutasiService $mutasiService)
    {
        $mutasi = \App\Models\MutasiUang::findOrFail($id);
        if ($mutasi->status !== 'draft') {
            return back()->with('error', 'Mutasi ini bukan draft.');
        }

        try {
            DB::beginTransaction();

            $mutasiService->kirimCashFisik(
                Auth::id(),
                $mutasi->cabang_asal_id,
                $mutasi->cabang_tujuan_id,
                $mutasi->nominal,
                $mutasi->keterangan
            );
            
            $mutasi->delete();

            DB::commit();
            return back()->with('success', 'Draft mutasi uang fisik berhasil diproses dan dikirim!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses draft uang fisik: ' . $e->getMessage());
        }
    }
}
