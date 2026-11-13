<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterBarang;
use App\Models\StokCabang;
use App\Models\PengaturanSistem;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\ApkLayanan;
use App\Models\SaldoApkLayanan;
use App\Models\ProdukDigitalA;
use App\Models\ProdukDigitalB;
use App\Services\DigitalBService;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    protected DigitalBService $digitalBService;
    protected InventoryService $inventoryService;

    public function __construct(DigitalBService $digitalBService, InventoryService $inventoryService)
    {
        $this->digitalBService = $digitalBService;
        $this->inventoryService = $inventoryService;
    }

    /**
     * Halaman Lobi / Dashboard Kasir.
     */
    public function dashboard()
    {
        $user = Auth::user();
        $cabangId = $user->cabang_id ?? 1;
        $cabang = $user->cabang->nama_cabang ?? 'Cabang Utama';

        // Hitung metrik penjualan hari ini untuk cabang kasir
        $todayQuery = \App\Models\Transaksi::where('cabang_id', $cabangId)
            ->whereDate('created_at', now()->toDateString());

        $transaksiSelesai = (clone $todayQuery)->count();
        $omsetHariIni = (clone $todayQuery)->sum('total_tagihan') ?? 0;
        $transaksiTunai = (clone $todayQuery)->where('metode_pembayaran', 'cash')->sum('total_tagihan') ?? 0;
        $transaksiTunaiCount = (clone $todayQuery)->where('metode_pembayaran', 'cash')->count();
        $transaksiNonTunai = (clone $todayQuery)->where('metode_pembayaran', '!=', 'cash')->sum('total_tagihan') ?? 0;
        $transaksiNonTunaiCount = (clone $todayQuery)->where('metode_pembayaran', '!=', 'cash')->count();

        // Saldo kas laci fisik saat ini di cabang
        $saldoKasLaci = \App\Models\KasCabang::where('cabang_id', $cabangId)->value('saldo') ?? 0;
            
        // Ambil riwayat transaksi terakhir untuk cabang ini (Maksimal 100 transaksi terbaru)
        $riwayatTransaksi = \App\Models\Transaksi::with(['detailTransaksi.barang.category', 'kasir'])
            ->where('cabang_id', $cabangId)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        // Cek EWS: Cari barang fisik yang stoknya <= batas_ews manual
        $stokFisik = \App\Models\StokCabang::with(['barang.category'])
            ->where('cabang_id', $cabangId)
            ->whereHas('barang', function($q) {
                $q->whereIn('tipe_barang', ['Fisik ACC', 'Fisik Voucher Kartu']);
            })
            ->get();
            
        $ewsKritis = $stokFisik->filter(function($stok) use ($cabangId) {
            $pengaturan = \App\Models\PengaturanEws::where('barang_id', $stok->barang_id)
                            ->where('cabang_id', $cabangId)
                            ->first();
            $threshold = $pengaturan ? $pengaturan->batas_ews : 20;
            return $stok->jumlah_stok <= $threshold;
        });

        // Hitung notifikasi mutasi (Stok & Cash) yang sedang dikirim ke cabang ini
        $pendingMutasiStok = \App\Models\MutasiStok::where('cabang_tujuan_id', $cabangId)
            ->where('status', 'dikirim')
            ->count();
        $pendingMutasiCash = \App\Models\MutasiUang::where('cabang_tujuan_id', $cabangId)
            ->where('jenis_mutasi', 'cash')
            ->where('status', 'dikirim')
            ->count();
        $pendingMutasiCount = $pendingMutasiStok + $pendingMutasiCash;

        return view('dashboards.kasir', compact(
            'transaksiSelesai',
            'omsetHariIni',
            'transaksiTunai',
            'transaksiTunaiCount',
            'transaksiNonTunai',
            'transaksiNonTunaiCount',
            'saldoKasLaci',
            'riwayatTransaksi',
            'cabang',
            'ewsKritis',
            'pendingMutasiCount'
        ));
    }

    /**
     * Halaman POS Utama Kasir (Full Screen).
     */
    public function index()
    {
        $user = Auth::user();
        $cabangId = $user->cabang_id;

        // Ambil stok cabang untuk barang fisik (hanya yang memiliki stok di cabang ini)
        $stokFisik = StokCabang::with(['barang'])
            ->where('cabang_id', $cabangId)
            ->whereHas('barang', function($q) {
                $q->whereIn('tipe_barang', ['Fisik ACC', 'Fisik Voucher Kartu']);
            })
            ->get()
            ->map(function ($stok) use ($cabangId) {
                // Cari HPP tertinggi (paling mahal) dari batch yang masih punya stok, 
                // atau HPP dari batch terakhir jika kosong, untuk batas diskon.
                $maxHpp = \App\Models\StokCabangBatch::where('cabang_id', $cabangId)
                    ->where('barang_id', $stok->barang_id)
                    ->where('stok_sisa_cabang', '>', 0)
                    ->max('hpp_batch');
                    
                if (!$maxHpp) {
                    $maxHpp = \App\Models\StokCabangBatch::where('cabang_id', $cabangId)
                        ->where('barang_id', $stok->barang_id)
                        ->max('hpp_batch') ?? 0;
                }
                
                $stok->hpp_tertinggi = (float) $maxHpp;
                return $stok;
            });

        // Ambil produk digital A (harga tetap) dari master_barang
        $produkDigitalA = MasterBarang::where('tipe_barang', 'Digital A')->get();

        // Ambil produk digital B (range fee) beserta ranges-nya
        // Karena ranges sekarang berelasi dengan master_barang, kita perlu relasi 'rangesDigitalB'
        // Namun karena kita belum mendefinisikannya di model MasterBarang, kita bisa join atau panggil relasi (buatkan relasinya)
        $produkDigitalB = MasterBarang::with('rangesDigitalB')->where('tipe_barang', 'Digital B')->get();

        // Ambil saldo Apk Layanan milik cabang ini
        $saldoApkLayanan = SaldoApkLayanan::with('apkLayanan')
            ->where('cabang_id', $cabangId)
            ->get();

        // Ambil daftar mutasi stok yang dikirim ke cabang ini
        $mutasiDikirim = \App\Models\MutasiStok::with(['detailMutasi.barang'])
            ->where('cabang_tujuan_id', $cabangId)
            ->where('status', 'dikirim')
            ->get();
            
        // Ambil daftar mutasi uang cash yang dikirim ke cabang ini
        $mutasiCashDikirim = \App\Models\MutasiUang::where('cabang_tujuan_id', $cabangId)
            ->where('jenis_mutasi', 'cash')
            ->where('status', 'dikirim')
            ->get();
            
        // Ambil hirarki kategori untuk filter
        $categoriesTree = \App\Models\Category::whereNull('parent_id')->with('children.children')->get();

        // Ambil daftar Apk Layanan yang di-assign ke cabang ini (untuk Digital A & B cashin)
        $apkLayananCabang = \App\Models\CabangAplikasiLayanan::with('masterAplikasiLayanan')
            ->where('cabang_id', $cabangId)
            ->get();

        // Ambil daftar Apk Layanan Pusat (untuk Digital B cashout)
        $apkLayananPusat = \App\Models\PusatAplikasiLayanan::with('masterAplikasiLayanan')->get();

        // Ambil daftar bantuan pusat pending untuk kasir ini
        $bantuanPusatPending = \App\Models\TransaksiBantuanPusat::where('kasir_id', $user->id)
            ->whereIn('status', ['menunggu_pusat', 'diproses_admin'])
            ->count();

        return view('dashboards.pos', compact(
            'stokFisik',
            'produkDigitalA',
            'produkDigitalB',
            'saldoApkLayanan',
            'mutasiDikirim',
            'mutasiCashDikirim',
            'categoriesTree',
            'apkLayananCabang',
            'apkLayananPusat',
            'bantuanPusatPending'
        ));
    }

    /**
     * Tampilkan struk digital QRIS setelah sukses.
     */
    public function showStrukQris($id)
    {
        $transaksi = Transaksi::with(['detailTransaksi.barang', 'kasir', 'cabang'])->findOrFail($id);

        if ($transaksi->metode_pembayaran !== 'qris') {
            return redirect()->route('kasir.dashboard')->with('error', 'Transaksi ini bukan menggunakan QRIS.');
        }

        return view('kasir.struk_qris', compact('transaksi'));
    }


    // =========================================================================
    // ALUR 1: CHECKOUT TRANSAKSI FISIK & DIGITAL A
    // Mendukung metode pembayaran: Cash & QRIS
    // Jika QRIS → otomatis pakai Apk Layanan yang is_tujuan_qris_utama = true
    // =========================================================================

    /**
     * Checkout untuk transaksi Barang Fisik dan Produk Digital A.
     *
     * Request body:
     * - metode_pembayaran: 'cash' | 'qris'
     * - uang_diterima: numeric (wajib jika cash)
     * - cart: JSON string berisi array item:
     *     Fisik:     { tipe: 'fisik', barang_id: int, kuantitas: int }
     *     Digital A: { tipe: 'digital_a', produk_digital_a_id: int, kuantitas: int }
     */
    public function checkoutFisikDigitalA(Request $request)
    {
        $request->validate([
            'metode_pembayaran' => 'required|in:cash,qris',
            'uang_diterima'     => 'nullable|numeric|min:0',
            'cart'              => 'required|string',
        ]);

        $user = Auth::user();
        $cabangId = $user->cabang_id;
        $cart = json_decode($request->cart, true);

        if (empty($cart)) {
            if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                return response()->json(['status' => 'error', 'message' => 'Keranjang belanja kosong.'], 400);
            }
            return back()->with('error', 'Keranjang belanja kosong.');
        }

        DB::beginTransaction();

        try {
            $totalTagihan = 0;
            $totalDiskonTransaksi = 0;

            // ── Jika QRIS: validasi ada Apk Layanan yang di-set sebagai Tujuan QRIS Utama di Pusat ──
            $pusatLayananQris = null;
            
            if ($request->metode_pembayaran === 'qris') {
                $pusatLayananQris = \App\Models\PusatAplikasiLayanan::where('is_qris_tujuan', true)->first();
                if (!$pusatLayananQris) {
                    throw new \Exception('Sistem menolak transaksi. Admin Pusat belum mengatur rekening untuk tujuan QRIS. Hubungi Owner!');
                }
            }

            // ── Buat Header Transaksi ──
            $transaksi = Transaksi::create([
                'kasir_id'           => $user->id,
                'cabang_id'          => $cabangId,
                'total_tagihan'      => 0,
                'uang_diterima'      => $request->metode_pembayaran === 'cash' ? $request->uang_diterima : null,
                'kembalian'          => 0,
                'metode_pembayaran'  => $request->metode_pembayaran,
                'tanggal_transaksi'  => now()->toDateString(),
            ]);

            // ── Loop setiap item di keranjang ──
            foreach ($cart as $item) {
                $tipe = $item['tipe'] ?? 'fisik'; // fisik / digital_a / digital_b

                if ($tipe === 'fisik') {
                    // ── BARANG FISIK (ACC / Voucher Kartu) ──
                    $barang = MasterBarang::findOrFail($item['barang_id']);
                    $kuantitas = (int) $item['kuantitas'];
                    
                    $hargaAsli = (float) $barang->harga_jual;
                    $diskonTotalRow = (float) ($item['diskon_total'] ?? 0);
                    $hargaFinalTotal = ($hargaAsli * $kuantitas) - $diskonTotalRow;
                    
                    // Validasi Batas Diskon (Khusus Fisik ACC)
                    if ($barang->tipe_barang === 'Fisik ACC') {
                        // Cari batch barang masuk tertua yang stok sisanya masih ada
                        $batchTertua = \App\Models\BarangMasuk::where('barang_fisik_id', $barang->id)
                                        ->where('stok_sisa', '>', 0)
                                        ->orderBy('id', 'asc')
                                        ->first();
                                        
                        if ($batchTertua) {
                            $batasModalTotal = $batchTertua->batas_diskon * $kuantitas;
                            if ($hargaFinalTotal < $batasModalTotal) {
                                throw new \Exception("Harga total terlalu rendah untuk " . $barang->nama_barang . ". Melewati batas limit modal sistem!");
                            }
                        } else {
                            $batchTerakhir = \App\Models\BarangMasuk::where('barang_fisik_id', $barang->id)
                                        ->orderBy('id', 'desc')
                                        ->first();
                            if ($batchTerakhir && $hargaFinalTotal < ($batchTerakhir->batas_diskon * $kuantitas)) {
                                throw new \Exception("Harga total terlalu rendah, melewati batas limit modal sistem!");
                            }
                        }
                    }

                    $subtotal = $hargaFinalTotal;
                    $totalTagihan += $subtotal;
                    $totalDiskonTransaksi += $diskonTotalRow;

                    // Validasi & kurangi stok cabang
                    $stokCabang = StokCabang::where('cabang_id', $cabangId)
                        ->where('barang_id', $barang->id)
                        ->lockForUpdate()
                        ->first();

                    if (!$stokCabang || $stokCabang->jumlah_stok < $kuantitas) {
                        throw new \Exception("Stok tidak mencukupi untuk: {$barang->nama_barang}");
                    }

                    $stokCabang->decrement('jumlah_stok', $kuantitas);

                    // Hitung HPP dan Potong Stok per Batch (FIFO)
                    $sisaKebutuhan = $kuantitas;
                    $batches = \App\Models\StokCabangBatch::where('cabang_id', $cabangId)
                                                          ->where('barang_id', $barang->id)
                                                          ->where('stok_sisa_cabang', '>', 0)
                                                          ->orderBy('created_at', 'asc')
                                                          ->lockForUpdate()
                                                          ->get();

                    $diskonPerPiece = $kuantitas > 0 ? ($diskonTotalRow / $kuantitas) : 0;

                    foreach ($batches as $batch) {
                        if ($sisaKebutuhan <= 0) break;

                        $qtyAmbil = min($batch->stok_sisa_cabang, $sisaKebutuhan);
                        $batch->decrement('stok_sisa_cabang', $qtyAmbil);
                        $sisaKebutuhan -= $qtyAmbil;

                        DetailTransaksi::create([
                            'transaksi_id'    => $transaksi->id,
                            'barang_id'       => $barang->id,
                            'harga_jual_final'=> $hargaAsli,
                            'diskon'          => $diskonPerPiece * $qtyAmbil,
                            'kuantitas'       => $qtyAmbil,
                            'hpp_satuan'      => $batch->hpp_batch,
                            'jenis_item'      => 'barang_fisik',
                        ]);
                    }

                    // Fallback jika karena alasan tertentu StokCabang sinkron tapi batch kurang
                    if ($sisaKebutuhan > 0) {
                        $fallbackHpp = $this->inventoryService->hitungHargaModalRataRata($barang->id);
                        DetailTransaksi::create([
                            'transaksi_id'    => $transaksi->id,
                            'barang_id'       => $barang->id,
                            'harga_jual_final'=> $hargaAsli,
                            'diskon'          => $diskonPerPiece * $sisaKebutuhan,
                            'kuantitas'       => $sisaKebutuhan,
                            'hpp_satuan'      => $fallbackHpp,
                            'jenis_item'      => 'barang_fisik',
                        ]);
                    }

                } elseif ($tipe === 'digital_a') {
                    // ── PRODUK DIGITAL A (Harga Tetap) ──
                    $produkA = MasterBarang::findOrFail($item['produk_digital_a_id']);
                    $kuantitas = (int) ($item['kuantitas'] ?? 1);
                    $hargaJualFinal = (float) $produkA->harga_jual;
                    $subtotal = $hargaJualFinal * $kuantitas;
                    $hargaModal = (float) ($item['harga_modal'] ?? 0);
                    $apkLayananId = $item['apk_layanan_id'] ?? null;

                    if (!$apkLayananId) {
                        throw new \Exception('Pilih Apk Layanan untuk produk Digital A: ' . $produkA->nama_barang);
                    }
                    if ($hargaModal <= 0) {
                        throw new \Exception('Harga Modal harus diisi untuk produk Digital A: ' . $produkA->nama_barang);
                    }

                    // Kurangi saldo Apk Layanan Cabang
                    $cabangApk = \App\Models\CabangAplikasiLayanan::where('id', $apkLayananId)
                        ->where('cabang_id', $cabangId)
                        ->lockForUpdate()
                        ->first();

                    if (!$cabangApk) {
                        throw new \Exception('Apk Layanan tidak ditemukan untuk cabang ini.');
                    }

                    if ($cabangApk->saldo_sekarang < $hargaModal) {
                        throw new \Exception('Saldo ' . ($cabangApk->masterAplikasiLayanan->nama_layanan ?? 'Apk') . ' tidak mencukupi! Saldo: Rp ' . number_format($cabangApk->saldo_sekarang, 0, ',', '.') . ', Modal: Rp ' . number_format($hargaModal, 0, ',', '.'));
                    }

                    $cabangApk->decrement('saldo_sekarang', $hargaModal);

                    DetailTransaksi::create([
                        'transaksi_id'    => $transaksi->id,
                        'barang_id'       => $produkA->id, 
                        'harga_jual_final'=> $hargaJualFinal,
                        'kuantitas'       => $kuantitas,
                        'hpp_satuan'      => $hargaModal,
                        'jenis_item'      => 'digital_a',
                        'cabang_apk_layanan_id' => $apkLayananId,
                    ]);

                    $totalTagihan += $subtotal;
                }
            }

            // ── Hitung kembalian & Update Kas Cabang (khusus Cash) ──
            $kembalian = null;
            if ($request->metode_pembayaran === 'cash') {
                $uangDiterima = (float) $request->uang_diterima;
                if ($uangDiterima < $totalTagihan) {
                    throw new \Exception(
                        "Uang diterima (Rp " . number_format($uangDiterima, 0, ',', '.') .
                        ") kurang dari total tagihan (Rp " . number_format($totalTagihan, 0, ',', '.') . ")."
                    );
                }
                $kembalian = $uangDiterima - $totalTagihan;
                
                // Tambahkan uang masuk ke tabel Cabang (Cash)
                $cabangModel = \App\Models\Cabang::findOrFail($cabangId);
                $cabangModel->increment('saldo_cash_sekarang', $totalTagihan);
            }

            // ── Jika QRIS: tambahkan saldo ke Apk Layanan Pusat Tujuan QRIS Utama ──
            if ($request->metode_pembayaran === 'qris' && $pusatLayananQris) {
                $pusatLayananQris->increment('saldo_sekarang', $totalTagihan);
                
                \App\Services\RiwayatApkService::catat(
                    'pusat', 
                    $pusatLayananQris->id, 
                    'masuk', 
                    $totalTagihan, 
                    'Pembayaran QRIS Transaksi #' . $transaksi->id, 
                    $transaksi
                );
            }

            // ── Update header transaksi ──
            $transaksi->update([
                'total_tagihan' => $totalTagihan,
                'total_diskon'  => $totalDiskonTransaksi,
                'kembalian'     => $kembalian,
            ]);

            DB::commit();

            if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Transaksi berhasil!',
                    'transaksi_id' => $transaksi->id,
                    'total' => $totalTagihan,
                    'kembalian' => $kembalian
                ]);
            }

            if ($request->metode_pembayaran === 'qris') {
                return redirect()->route('kasir.struk_qris', ['id' => $transaksi->id])
                                 ->with('success', 'Transaksi berhasil! Total: Rp ' . number_format($totalTagihan, 0, ',', '.'));
            }

            return redirect()->route('kasir.dashboard')->with('success',
                'Transaksi berhasil! Total: Rp ' . number_format($totalTagihan, 0, ',', '.'));

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    // =========================================================================
    // ALUR 2: CHECKOUT TRANSAKSI DIGITAL B (CashIn & CashOut)
    // Digital B punya endpoint sendiri, terpisah dari Fisik + Digital A
    // =========================================================================

    /**
     * Checkout Digital B - CashIn (Topup/TF) & CashOut (Tarik Tunai)
     */
    public function checkoutDigitalB(Request $request)
    {
        $user = Auth::user();
        $cabangId = $user->cabang_id;

        DB::beginTransaction();

        try {
            $barangId = (int) $request->barang_id;
            $produkB = MasterBarang::findOrFail($barangId);
            $jenisTransaksi = $produkB->jenis_transaksi; // cash_in or cash_out
            $hargaModal = (float) $request->harga_modal;
            $apkLayananId = (int) $request->apk_layanan_id;

            if ($hargaModal <= 0) {
                throw new \Exception('Harga Modal harus diisi dan lebih dari 0!');
            }

            // Validasi fee berdasarkan harga modal
            $validasi = $this->digitalBService->validasiDanCariFee($produkB->id, $hargaModal);
            if (!$validasi['success']) {
                throw new \Exception($validasi['message']);
            }
            $fee = $validasi['fee'];

            if ($jenisTransaksi === 'cash_in') {
                // === CASHIN: Topup/TF ===
                // Total bayar pelanggan = harga_modal + fee
                $totalBayar = $hargaModal + $fee;

                // Kurangi saldo apk layanan cabang
                $cabangApk = \App\Models\CabangAplikasiLayanan::where('id', $apkLayananId)
                    ->where('cabang_id', $cabangId)
                    ->lockForUpdate()
                    ->first();

                if (!$cabangApk) {
                    throw new \Exception('Apk Layanan Cabang tidak ditemukan.');
                }
                if ($cabangApk->saldo_sekarang < $hargaModal) {
                    throw new \Exception('Saldo ' . ($cabangApk->masterAplikasiLayanan->nama_layanan ?? 'Apk') . ' tidak mencukupi! Saldo: Rp ' . number_format($cabangApk->saldo_sekarang, 0, ',', '.'));
                }

                $cabangApk->decrement('saldo_sekarang', $hargaModal);

                // Cash cabang bertambah (pelanggan bayar tunai)
                $cabangModel = \App\Models\Cabang::findOrFail($cabangId);
                $cabangModel->increment('saldo_cash_sekarang', $totalBayar);

                // Buat transaksi
                $transaksi = Transaksi::create([
                    'kasir_id' => $user->id,
                    'cabang_id' => $cabangId,
                    'total_tagihan' => $totalBayar,
                    'uang_diterima' => $totalBayar,
                    'kembalian' => 0,
                    'metode_pembayaran' => 'cash',
                    'tanggal_transaksi' => now()->toDateString(),
                ]);

                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'barang_id' => $produkB->id,
                    'harga_jual_final' => $totalBayar,
                    'kuantitas' => 1,
                    'hpp_satuan' => $hargaModal,
                    'jenis_item' => 'digital_b',
                    'cabang_apk_layanan_id' => $apkLayananId,
                ]);

                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Transaksi CashIn berhasil!',
                    'data' => [
                        'produk' => $produkB->nama_barang,
                        'harga_modal' => $hargaModal,
                        'fee' => $fee,
                        'total_bayar' => $totalBayar,
                        'transaksi_id' => $transaksi->id,
                    ]
                ]);

            } elseif ($jenisTransaksi === 'cash_out') {
                // === CASHOUT: Tarik Tunai ===
                // Pelanggan TF ke pusat, kasir kasih uang tunai
                // Uang yang kasir keluarkan = harga_modal
                $uangKeluar = $hargaModal;
                $totalBayar = $hargaModal + $fee;

                // Saldo pusat bertambah (pelanggan TF ke pusat sebesar modal + fee)
                $pusatApk = \App\Models\PusatAplikasiLayanan::where('id', $apkLayananId)
                    ->lockForUpdate()
                    ->first();

                if (!$pusatApk) {
                    throw new \Exception('Apk Layanan Pusat tidak ditemukan.');
                }

                $pusatApk->increment('saldo_sekarang', $totalBayar);

                // Cash cabang berkurang (kasir keluarkan uang)
                $cabangModel = \App\Models\Cabang::findOrFail($cabangId);
                if ($cabangModel->saldo_cash_sekarang < $uangKeluar) {
                    throw new \Exception('Saldo kas cabang tidak mencukupi untuk tarik tunai!');
                }
                $cabangModel->decrement('saldo_cash_sekarang', $uangKeluar);

                // Buat transaksi
                $transaksi = Transaksi::create([
                    'kasir_id' => $user->id,
                    'cabang_id' => $cabangId,
                    'total_tagihan' => $totalBayar, // Pendapatan dihitung dari selisih dengan HPP
                    'uang_diterima' => null,
                    'kembalian' => null,
                    'metode_pembayaran' => 'cash',
                    'tanggal_transaksi' => now()->toDateString(),
                ]);

                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'barang_id' => $produkB->id,
                    'harga_jual_final' => $totalBayar,
                    'kuantitas' => 1,
                    'hpp_satuan' => $hargaModal,
                    'jenis_item' => 'digital_b',
                    'pusat_apk_layanan_id' => $apkLayananId,
                ]);

                \App\Services\RiwayatApkService::catat(
                    'pusat', 
                    $pusatApk->id, 
                    'masuk', 
                    $totalBayar, 
                    'Tarik Tunai / Cash Out Pelanggan', 
                    $transaksi
                );

                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Transaksi CashOut berhasil!',
                    'data' => [
                        'produk' => $produkB->nama_barang,
                        'nominal' => $hargaModal,
                        'fee' => $fee,
                        'uang_keluar' => $uangKeluar,
                        'transaksi_id' => $transaksi->id,
                    ]
                ]);
            } else {
                throw new \Exception('Jenis transaksi produk tidak valid.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    // =========================================================================
    // ALUR 3: BANTUAN PUSAT (Kasir request admin TF/topup besar)
    // =========================================================================

    /**
     * Kasir kirim request bantuan pusat untuk CashIn besar.
     */
    public function requestBantuanPusat(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|exists:master_barang,id',
            'nominal' => 'required|numeric|min:1',
            'nomor_tujuan' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        $user = Auth::user();
        $produk = MasterBarang::findOrFail($request->barang_id);

        // Fee dan total bayar akan dihitung oleh Admin Pusat berdasarkan modal asli
        $fee = 0;
        $totalBayar = 0;

        \App\Models\TransaksiBantuanPusat::create([
            'kasir_id' => $user->id,
            'cabang_id' => $user->cabang_id,
            'barang_id' => $request->barang_id,
            'nomor_tujuan' => $request->nomor_tujuan ?? '',
            'keterangan' => $request->keterangan,
            'amount' => $request->nominal,
            'status' => 'menunggu_pusat',
            'fee_cabang' => $fee,
            'total_bayar_pelanggan' => $totalBayar,
        ]);

        if ($request->expectsJson() || $request->ajax() || $request->isJson()) {
            return response()->json(['status' => 'success', 'message' => 'Request bantuan pusat berhasil dikirim!']);
        }

        return redirect()->back()->with('success', 'Request bantuan pusat berhasil dikirim!');
    }

    /**
     * Kasir lihat daftar request bantuan pusat miliknya.
     */
    public function daftarBantuanKasir()
    {
        $user = Auth::user();
        $daftar = \App\Models\TransaksiBantuanPusat::with(['barang', 'admin', 'apkLayananPusat.masterAplikasiLayanan'])
            ->where('kasir_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return view('kasir.bantuan_pusat.index', compact('daftar'));
    }

    /**
     * Kasir konfirmasi bantuan pusat setelah admin proses & uang diterima.
     */
    public function konfirmasiBantuanKasir($id)
    {
        $user = Auth::user();

        DB::beginTransaction();
        try {
            $bantuan = \App\Models\TransaksiBantuanPusat::where('id', $id)
                ->where('kasir_id', $user->id)
                ->where('status', 'diproses_admin')
                ->firstOrFail();

            $totalBayar = $bantuan->total_bayar_pelanggan;
            $modalPusat = $bantuan->harga_modal_pusat;
            $cabangModel = \App\Models\Cabang::findOrFail($bantuan->cabang_id);
            $jenisTransaksi = $bantuan->barang->jenis_transaksi;

            if ($jenisTransaksi === 'cash_in') {
                // Cash cabang bertambah (pelanggan sudah bayar ke kasir)
                $cabangModel->increment('saldo_cash_sekarang', $totalBayar);
                $uangDiterima = $totalBayar;
                $kembalian = 0;
            } else {
                // Cash out (Tarik Tunai)
                // Cash cabang berkurang (kasir ngasih tunai ke pelanggan sebesar modal)
                if ($cabangModel->saldo_cash_sekarang < $modalPusat) {
                    throw new \Exception('Saldo cash cabang tidak mencukupi untuk diberikan ke pelanggan!');
                }
                $cabangModel->decrement('saldo_cash_sekarang', $modalPusat);
                $uangDiterima = null;
                $kembalian = null;
            }

            // Buat record transaksi
            $transaksi = Transaksi::create([
                'kasir_id' => $user->id,
                'cabang_id' => $bantuan->cabang_id,
                'total_tagihan' => $totalBayar,
                'uang_diterima' => $uangDiterima,
                'kembalian' => $kembalian,
                'metode_pembayaran' => 'cash',
                'tanggal_transaksi' => now()->toDateString(),
                'is_bantuan_pusat' => true,
            ]);

            DetailTransaksi::create([
                'transaksi_id' => $transaksi->id,
                'barang_id' => $bantuan->barang_id,
                'harga_jual_final' => $totalBayar,
                'kuantitas' => 1,
                'hpp_satuan' => $modalPusat,
                'jenis_item' => 'digital_b',
                'pusat_apk_layanan_id' => $bantuan->pusat_aplikasi_layanan_id ?? null,
            ]);

            $bantuan->update(['status' => 'selesai']);

            DB::commit();

            $msg = $jenisTransaksi === 'cash_in' 
                ? 'Bantuan pusat berhasil dikonfirmasi! Laci cabang bertambah Rp ' . number_format($totalBayar, 0, ',', '.')
                : 'Tarik Tunai berhasil dikonfirmasi! Laci cabang berkurang Rp ' . number_format($modalPusat, 0, ',', '.');

            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    
    public function batalBantuanPusat($id)
    {
        $user = Auth::user();
        $bantuan = \App\Models\TransaksiBantuanPusat::where('id', $id)
            ->where('kasir_id', $user->id)
            ->where('status', 'menunggu_pusat')
            ->firstOrFail();
            
        $bantuan->delete();
        
        return redirect()->back()->with('success', 'Request Bantuan Pusat (Digital B) berhasil dibatalkan.');
    }

    /**
     * Endpoint untuk memproses data sinkronisasi dari IndexedDB PWA.
     */
    public function syncOffline(Request $request)
    {
        $transactions = $request->json()->all();
        
        $syncedIds = [];
        $errors = [];

        foreach ($transactions as $offlineTrx) {
            try {
                $cartPayload = $offlineTrx['payload']['cart'];
                if (is_array($cartPayload)) {
                    $cartPayload = json_encode($cartPayload);
                }

                // Rekonstruksi Request buatan
                $fakeRequest = new Request([
                    'cart' => $cartPayload,
                    'metode_pembayaran' => $offlineTrx['payload']['metode_pembayaran'],
                    'uang_diterima' => $offlineTrx['payload']['uang_diterima']
                ]);

                // Set agar respon checkout berupa JSON, sehingga tidak me-return redirect
                $fakeRequest->headers->set('Accept', 'application/json');

                // Eksekusi checkout
                $this->checkoutFisikDigitalA($fakeRequest);
                
                $syncedIds[] = $offlineTrx['id']; // ID dari IndexedDB
            } catch (\Exception $e) {
                // Catat error jika ada gagal pada satu transaksi
                $errors[] = [
                    'id' => $offlineTrx['id'],
                    'message' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'synced_ids' => $syncedIds,
            'errors' => $errors
        ]);
    }

    public function ewsDetail($barangId, \App\Services\SesForecastingService $sesService)
    {
        $user = Auth::user();
        $cabangId = $user->cabang_id;

        $barang = MasterBarang::findOrFail($barangId);
        $stok = StokCabang::where('cabang_id', $cabangId)->where('barang_id', $barangId)->first();
        $jumlahStok = $stok ? $stok->jumlah_stok : 0;

        $pengaturan = \App\Models\PengaturanEws::where('barang_id', $barangId)->where('cabang_id', $cabangId)->first();

        // Ambil pengaturan hari peramalan, default 30
        $rangeDays = $pengaturan->hari_peramalan ?? 30;
        $targetDate = \Carbon\Carbon::now()->addDays($rangeDays)->format('d-m-Y');
        
        $prediksi = $sesService->calculateDynamicForecast($cabangId, $barangId, $rangeDays);
        $ramalan = $prediksi['success'] ? $prediksi['nilai_peramalan'] : 0;
        
        // Safety Stock = berdasarkan setting persen per barang
        $safetyStockPercent = $pengaturan->safety_stock_percent ?? 20;
        $safetyStock = ceil($ramalan * ($safetyStockPercent / 100));
        $rekomendasiRestok = max(0, ($ramalan + $safetyStock) - $jumlahStok);

        return view('dashboards.ews_detail', compact(
            'barang', 'jumlahStok', 'ramalan', 'safetyStock', 'rekomendasiRestok', 'prediksi', 'rangeDays', 'targetDate', 'safetyStockPercent'
        ));
    }

    public function ewsMintaBantuan(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|exists:master_barang,id'
        ]);

        $user = Auth::user();

        DB::beginTransaction();
        try {
            // Buat request operasional (tanpa nominal_request atau kuantitas karena ditentukan admin)
            $req = \App\Models\RequestOperasional::create([
                'kasir_id' => $user->id,
                'cabang_id' => $user->cabang_id,
                'jenis_request' => 'barang_fisik',
                'status' => 'menunggu',
                'status_request' => 'menunggu_admin',
                'keterangan' => 'Peringatan EWS: Restok ' . MasterBarang::find($request->barang_id)->nama_barang,
            ]);

            \App\Models\DetailRequestOperasional::create([
                'request_id' => $req->id,
                'barang_fisik_id' => $request->barang_id,
                'nominal_atau_qty' => 0, // Admin yang akan mengisi kuantitasnya nanti
            ]);

            DB::commit();
            return redirect()->route('kasir.dashboard')->with('success', 'Permintaan Bantuan Restok telah dikirim ke Admin Pusat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal meminta bantuan: ' . $e->getMessage());
        }
    }
}
