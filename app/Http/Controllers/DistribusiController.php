<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cabang;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\StokPusat;

class DistribusiController extends Controller
{
    /**
     * Tampilkan antarmuka Monitoring dan Distribusi
     */
    public function index()
    {
        $cabangs = Cabang::orderBy('nama_cabang', 'asc')->get();
        $groupedCabangs = $cabangs->groupBy(function($item, $key) {
            return $item->area ?? 'Tanpa Area';
        });

        // Ambil stok berdasarkan batch dari BarangMasuk yang sisa stoknya > 0
        $stokPusatBatches = \App\Models\BarangMasuk::with(['barang.category', 'supplier', 'pembelian'])->where('stok_sisa', '>', 0)->orderBy('created_at', 'asc')->get();
        
        $totalAsetModalPusat = 0;
        $totalAsetJualPusat = 0;
        $stokPusatGrouped = [];
        
        $totalStokPusatPerBarang = DB::table('barang_masuk')
            ->select('barang_fisik_id', DB::raw('SUM(stok_sisa) as total_stok'))
            ->groupBy('barang_fisik_id')
            ->pluck('total_stok', 'barang_fisik_id');

        foreach ($stokPusatBatches as $batch) {
            if (!$batch->barang) continue;
            
            $hargaModal = $batch->qty_beli > 0 ? ($batch->total_harga_beli / $batch->qty_beli) : 0;
            $hargaJual = $batch->barang->harga_jual;
            $qty = $batch->stok_sisa;
            
            $subtotalModal = $hargaModal * $qty;
            $subtotalJual = $hargaJual * $qty;
            
            $totalAsetModalPusat += $subtotalModal;
            $totalAsetJualPusat += $subtotalJual;

            // EWS Logic for Gudang Pusat
            $is_kritis = false;
            $batas_ews = 0;
            $hari_peramalan = 0;
            $rekomendasi_restok = 0;

            if (in_array($batch->barang->tipe_barang, ['Fisik ACC', 'Fisik Voucher Kartu'])) {
                $totalStok = $totalStokPusatPerBarang[$batch->barang_fisik_id] ?? 0;
                $pengaturan = \App\Models\PengaturanEws::where('barang_id', $batch->barang_fisik_id)
                    ->whereNull('cabang_id')
                    ->first();
                $threshold = $pengaturan ? $pengaturan->batas_ews : 20;
                $batas_ews = $threshold;
                $hari_peramalan = $pengaturan ? $pengaturan->hari_peramalan : 31;
                
                if ($totalStok <= $threshold) {
                    $is_kritis = true;
                    // Heuristik sederhana: Rekomendasi = (Batas * 2) - Sisa Stok, minimal 5
                    $rekomendasi_restok = max(5, ($threshold * 2) - $totalStok);
                }
            }
            
            $bId = $batch->barang_fisik_id;
            if (!isset($stokPusatGrouped[$bId])) {
                $stokPusatGrouped[$bId] = [
                    'barang_id' => $bId,
                    'nama_barang' => $batch->barang->nama_barang,
                    'kategori' => $batch->barang->category ? $batch->barang->category->name : '-',
                    'tipe_barang' => $batch->barang->tipe_barang,
                    'total_stok' => 0,
                    'total_aset_modal' => 0,
                    'total_aset_jual' => 0,
                    'potensi_laba' => 0,
                    'is_kritis' => $is_kritis,
                    'batas_ews' => $batas_ews,
                    'hari_peramalan' => $hari_peramalan,
                    'rekomendasi_restok' => $rekomendasi_restok,
                    'oldest_batch_date' => $batch->created_at->toIso8601String(),
                    'newest_batch_date' => $batch->created_at->toIso8601String(),
                    'batches' => []
                ];
            }

            $stokPusatGrouped[$bId]['total_stok'] += $qty;
            $stokPusatGrouped[$bId]['total_aset_modal'] += $subtotalModal;
            $stokPusatGrouped[$bId]['total_aset_jual'] += $subtotalJual;
            $stokPusatGrouped[$bId]['potensi_laba'] = $stokPusatGrouped[$bId]['total_aset_jual'] - $stokPusatGrouped[$bId]['total_aset_modal'];
            $stokPusatGrouped[$bId]['newest_batch_date'] = $batch->created_at->toIso8601String(); // Because sorted asc initially

            $stokPusatGrouped[$bId]['batches'][] = [
                'kode_batch' => $batch->kode_batch ?: 'BATCH-'.$batch->id,
                'usia_hari' => (int) floor(\Carbon\Carbon::parse($batch->created_at)->diffInDays(\Carbon\Carbon::now())),
                'tanggal_masuk' => \Carbon\Carbon::parse($batch->created_at)->translatedFormat('d M Y'),
                'supplier' => $batch->supplier ? $batch->supplier->nama_supplier : 'Tanpa Supplier',
                'stok' => $qty,
                'harga_modal_raw' => $hargaModal,
                'subtotal_modal_raw' => $subtotalModal,
                'harga_modal' => 'Rp ' . number_format($hargaModal, 0, ',', '.'),
                'subtotal_modal' => 'Rp ' . number_format($subtotalModal, 0, ',', '.')
            ];
        }

        $stokPusatGrouped = array_values($stokPusatGrouped); // Re-index for alpine
        
        // Sort grouped array by is_kritis true first
        usort($stokPusatGrouped, function ($a, $b) {
            if ($a['is_kritis'] === $b['is_kritis']) {
                return strcmp($a['nama_barang'], $b['nama_barang']);
            }
            return $a['is_kritis'] ? -1 : 1;
        });

        // --- Rekap Stok Fisik per Cabang dengan EWS ---
        $semuaCabangs = Cabang::orderBy('nama_cabang', 'asc')->get();
        $rekapCabang = [];

        foreach ($semuaCabangs as $cabang) {
            $has_ews = false;
            $ews_count = 0;
            $totalBarang = 0;

            $stokCabangBatches = \App\Models\StokCabangBatch::with(['barang.category'])
                ->where('cabang_id', $cabang->id)
                ->where('stok_sisa_cabang', '>', 0)
                ->get();

            $groupedItems = [];
            foreach ($stokCabangBatches as $scb) {
                if (!$scb->barang) continue;
                if (!in_array($scb->barang->tipe_barang, ['Fisik ACC', 'Fisik Voucher Kartu'])) continue;

                $bid = $scb->barang_id;
                if (!isset($groupedItems[$bid])) {
                    $pengaturanCabang = \App\Models\PengaturanEws::where('barang_id', $bid)
                        ->where('cabang_id', $cabang->id)
                        ->first();
                    $threshold = $pengaturanCabang ? $pengaturanCabang->batas_ews : 20;
                    $hariPeramalan = $pengaturanCabang ? $pengaturanCabang->hari_peramalan : 31;

                    $groupedItems[$bid] = [
                        'barang_id'          => $bid,
                        'nama_barang'        => $scb->barang->nama_barang,
                        'kategori'           => $scb->barang->category ? $scb->barang->category->name : '-',
                        'tipe_barang'        => $scb->barang->tipe_barang,
                        'harga_modal'        => $scb->barang->harga_modal ?? 0,
                        'harga_jual'         => $scb->barang->harga_jual ?? 0,
                        'total_stok'         => 0,
                        'batas_ews'          => $threshold,
                        'hari_peramalan'     => $hariPeramalan,
                        'is_kritis'          => false,
                        'rekomendasi_restok' => 0,
                        'total_aset_modal'   => 0,
                        'potensi_laba'       => 0,
                    ];
                }
                $groupedItems[$bid]['total_stok'] += $scb->stok_sisa_cabang;
            }

            foreach ($groupedItems as $bid => &$item) {
                $totalBarang++;
                if ($item['total_stok'] <= $item['batas_ews']) {
                    $item['is_kritis'] = true;
                    $item['rekomendasi_restok'] = max(5, ($item['batas_ews'] * 2) - $item['total_stok']);
                    $has_ews = true;
                    $ews_count++;
                }
                $item['total_aset_modal'] = ($item['harga_modal'] ?? 0) * $item['total_stok'];
                $item['potensi_laba'] = (($item['harga_jual'] ?? 0) - ($item['harga_modal'] ?? 0)) * $item['total_stok'];
            }
            unset($item);

            // Sort kritis first
            usort($groupedItems, fn($a, $b) => $b['is_kritis'] <=> $a['is_kritis']);

            $rekapCabang[] = [
                'cabang'       => $cabang,
                'items'        => array_values($groupedItems),
                'total_barang' => $totalBarang,
                'has_ews'      => $has_ews,
                'ews_count'    => $ews_count,
            ];
        }

        // Sort cabang: EWS first
        usort($rekapCabang, fn($a, $b) => $b['has_ews'] <=> $a['has_ews']);

        // Buat daftar khusus untuk form Mutasi Gudang Pusat (memasukkan semua barang fisik)
        $semuaBarang = \App\Models\MasterBarang::whereIn('tipe_barang', ['Fisik ACC', 'Fisik Voucher Kartu'])->get();
        $stokMutasiPusat = [];
        $groupedPusat = [];
        foreach ($semuaBarang as $b) {
            $groupedPusat[$b->id] = ['barang_id' => $b->id, 'nama_tampil' => $b->nama_barang, 'tipe_barang' => $b->tipe_barang, 'stok' => 0];
        }
        foreach ($stokPusatBatches as $batch) {
            if (isset($groupedPusat[$batch->barang_fisik_id])) {
                $groupedPusat[$batch->barang_fisik_id]['stok'] += $batch->stok_sisa;
            }
        }
        $stokMutasiPusat = array_values($groupedPusat);

        // Auto-populate from Request Operasional OR EWS Quick Restock / Direct Mutasi Link
        $requestItems = [];
        $requestId = request('request_id');
        if ($requestId) {
            $reqOp = \App\Models\RequestOperasional::with('detail.barangFisik')->find($requestId);
            if ($reqOp) {
                foreach ($reqOp->detail as $dtl) {
                    if ($dtl->barang_fisik_id) {
                        $requestItems[] = [
                            'id' => uniqid(),
                            'cabang_asal_id' => '', // Default pusat
                            'cabang_tujuan_id' => (string) $reqOp->cabang_id,
                            'barang_id' => (string) $dtl->barang_fisik_id,
                            'nama_barang' => $dtl->barangFisik->nama_barang ?? 'Unknown',
                            'qty' => $dtl->nominal_atau_qty
                        ];
                    }
                }
            }
        } elseif (request('barang_id') || request('cabang_tujuan') || request('cabang_id')) {
            $barangId = request('barang_id');
            $cabangTujuan = request('cabang_tujuan') ?? request('cabang_id');
            $qty = (int) request('qty', 1);
            $barang = $barangId ? \App\Models\MasterBarang::find($barangId) : null;
            
            $requestItems[] = [
                'id' => uniqid(),
                'cabang_asal_id' => (string) request('cabang_asal', ''), // Default: '' (Gudang Pusat Utama)
                'cabang_tujuan_id' => (string) ($cabangTujuan ?? ''),
                'barang_id' => (string) ($barangId ?? ''),
                'nama_barang' => $barang ? $barang->nama_barang : '',
                'qty' => max(1, $qty)
            ];
        }

        // --- Riwayat Mutasi (Barang & Cash) ---
        $draftMutasiStok = \App\Models\MutasiStok::with(['cabangAsal', 'cabangTujuan', 'detailMutasi.barang', 'admin'])
                                ->where('status', 'draft')
                                ->orderBy('created_at', 'desc')
                                ->get();
                                
        $draftMutasiCash = \App\Models\MutasiUang::with(['cabangAsal', 'cabangTujuan', 'admin'])
                                ->where('status', 'draft')
                                ->orderBy('created_at', 'desc')
                                ->get();

        $riwayatMutasiStok = \App\Models\MutasiStok::with(['cabangAsal', 'cabangTujuan', 'detailMutasi.barang', 'admin'])
                                ->where('status', '!=', 'draft')
                                ->orderBy('created_at', 'desc')
                                ->limit(100)
                                ->get();
                                
        $riwayatMutasiCash = \App\Models\MutasiUang::with(['cabangAsal', 'cabangTujuan', 'admin'])
                                ->where('status', '!=', 'draft')
                                ->orderBy('created_at', 'desc')
                                ->limit(100)
                                ->get();
        // --------------------------------------

        return view('distribusi_mutasi.index', compact(
            'groupedCabangs', 'cabangs', 'stokPusatGrouped', 'stokMutasiPusat', 'totalAsetModalPusat', 'totalAsetJualPusat',
            'riwayatMutasiStok', 'riwayatMutasiCash', 'draftMutasiStok', 'draftMutasiCash', 'requestItems', 'rekapCabang'
        ));
    }

    /**
     * Endpoint API JSON untuk mengambil statistik finansial Cabang Hari Ini
     */
    public function getStats($cabangId)
    {
        $cabang = Cabang::with(['aplikasiLayanan.masterAplikasiLayanan'])->findOrFail($cabangId);
        $today = Carbon::today();

        // Cash Masuk = Semua Transaksi tipe cash hari ini dimana kasir menerima uang (uang_diterima tidak null)
        $cashMasuk = Transaksi::where('cabang_id', $cabangId)
            ->whereDate('tanggal_transaksi', $today)
            ->where('metode_pembayaran', 'cash')
            ->whereNotNull('uang_diterima')
            ->sum('total_tagihan');

        // Cash Keluar = Semua Transaksi tipe cash hari ini dimana kasir tidak menerima uang / memberikan uang (Tarik Tunai)
        $cashKeluar = Transaksi::where('cabang_id', $cabangId)
            ->whereDate('tanggal_transaksi', $today)
            ->where('metode_pembayaran', 'cash')
            ->whereNull('uang_diterima')
            ->sum('total_tagihan');

        // Total Pendapatan Cash Hari Ini
        $totalPendapatan = $cabang->saldo_awal_cash + $cashMasuk - $cashKeluar;

        return response()->json([
            'success' => true,
            'cabang_id' => $cabang->id,
            'nama_cabang' => $cabang->nama_cabang,
            'saldo_awal_cash' => (float) $cabang->saldo_awal_cash,
            'cash_masuk_hari_ini' => (float) $cashMasuk,
            'cash_keluar_hari_ini' => (float) $cashKeluar,
            'total_pendapatan_hari_ini' => (float) $totalPendapatan,
            'layanan' => $cabang->aplikasiLayanan->map(function ($layanan) {
                return [
                    'id' => $layanan->id,
                    'nama_layanan' => $layanan->masterAplikasiLayanan ? $layanan->masterAplikasiLayanan->nama_layanan : 'Unknown',
                    'jenis_layanan' => $layanan->masterAplikasiLayanan ? $layanan->masterAplikasiLayanan->jenis_layanan : 'Unknown',
                    'nomor_rekening' => $layanan->nomor_rekening,
                    'saldo_sekarang' => (float) $layanan->saldo_sekarang,
                    'is_qris_tujuan' => (bool) $layanan->is_qris_tujuan,
                ];
            }),
        ]);
    }

    /**
     * Endpoint API JSON untuk mengambil Saldo Kas (Pusat atau Cabang)
     */
    public function getSaldoKas($cabangId = null)
    {
        try {
            if (empty($cabangId)) {
                $kas = \App\Models\KasPusat::first();
                return response()->json([
                    'success' => true,
                    'saldo' => $kas ? (float) $kas->saldo : 0,
                    'lokasi' => 'Gudang Pusat'
                ]);
            } else {
                $kas = \App\Models\KasCabang::where('cabang_id', $cabangId)->first();
                $cabang = \App\Models\Cabang::find($cabangId);
                return response()->json([
                    'success' => true,
                    'saldo' => $kas ? (float) $kas->saldo : 0,
                    'lokasi' => $cabang ? $cabang->nama_cabang : 'Cabang'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getStokAsal($cabang_id = null)
    {
        $semuaBarang = \App\Models\MasterBarang::whereIn('tipe_barang', ['Fisik ACC', 'Fisik Voucher Kartu'])->get();
        $grouped = [];
        
        foreach ($semuaBarang as $barang) {
            $grouped[$barang->id] = [
                'barang_id' => $barang->id,
                'nama_tampil' => $barang->nama_barang,
                'tipe_barang' => $barang->tipe_barang,
                'stok' => 0,
            ];
        }

        if (!$cabang_id) {
            // Gudang Pusat
            $stokPusatBatches = \App\Models\BarangMasuk::where('stok_sisa', '>', 0)->get();
            foreach ($stokPusatBatches as $batch) {
                if (isset($grouped[$batch->barang_fisik_id])) {
                    $grouped[$batch->barang_fisik_id]['stok'] += $batch->stok_sisa;
                }
            }
        } else {
            // Stok Cabang
            $stokCabang = \App\Models\StokCabangBatch::where('cabang_id', $cabang_id)->where('stok_sisa_cabang', '>', 0)->get();
            foreach ($stokCabang as $batch) {
                if (isset($grouped[$batch->barang_id])) {
                    $grouped[$batch->barang_id]['stok'] += $batch->stok_sisa_cabang;
                }
            }
        }

        $stokDetails = array_values($grouped);
        return response()->json(['success' => true, 'data' => $stokDetails]);
    }
}
