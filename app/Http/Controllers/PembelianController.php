<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PembelianSupplier;
use App\Models\DetailPembelian;
use App\Models\KasPusat;
use App\Models\SaldoApkLayanan;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PembelianController extends Controller
{
    public function index()
    {
        $pembelians = PembelianSupplier::with(['supplier', 'detailPembelian.barangFisik'])->orderBy('created_at', 'desc')->get();
        return view('admin.pembelian.index', compact('pembelians'));
    }

    public function create()
    {
        $suppliers = \App\Models\Supplier::all();
        $barangs = \App\Models\MasterBarang::whereIn('tipe_barang', ['Fisik ACC', 'Fisik Voucher Kartu'])->get();
        $dompetPusat = \App\Models\PusatAplikasiLayanan::with('masterAplikasiLayanan')->get();
        $kasPusat = KasPusat::first();

        // Hitung stok aktual saat ini di Gudang Pusat untuk setiap barang
        $stokPusat = DB::table('barang_masuk')
            ->where('stok_sisa', '>', 0)
            ->groupBy('barang_fisik_id')
            ->select('barang_fisik_id', DB::raw('SUM(stok_sisa) as total_stok'))
            ->pluck('total_stok', 'barang_fisik_id');

        $barangs->each(function($b) use ($stokPusat) {
            $b->stok_gudang = (int) ($stokPusat[$b->id] ?? 0);
        });

        // Cek jika terhubung dari Request Operasional Cabang
        $requestOp = null;
        if (request('request_id')) {
            $requestOp = \App\Models\RequestOperasional::with(['cabang', 'detail.barangFisik'])->find(request('request_id'));
        }

        return view('admin.pembelian.create', compact('suppliers', 'barangs', 'dompetPusat', 'kasPusat', 'requestOp'));
    }

    /**
     * Membuat draft pembelian baru.
     */
    public function createDraft(Request $request, InventoryService $inventoryService)
    {
        $request->validate([
            'action'          => 'required|in:draft,langsung_beli',
            'nama_supplier'   => 'nullable|string|max:255',
            'kontak_supplier' => 'nullable|string|max:255',
            'items'           => 'required|array|min:1',
            'items.*.barang_fisik_id' => 'required|exists:master_barang,id',
            'items.*.qty'     => 'required|integer|min:1',
            'items.*.subtotal'=> $request->action === 'draft' ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'metode_pembayaran' => 'required_if:action,langsung_beli|in:cash_pusat,apk_layanan',
            'apk_layanan_id'    => 'nullable|required_if:metode_pembayaran,apk_layanan|exists:pusat_aplikasi_layanan,id',
        ]);

        $namaSupplier = $request->nama_supplier ?: 'Umum';

        $supplier = \App\Models\Supplier::firstOrCreate(
            ['nama_supplier' => $namaSupplier],
            ['kontak' => $request->kontak_supplier ?? '-']
        );

        DB::beginTransaction();
        try {
            // Hitung total harga berdasarkan subtotal yang diinput
            $totalHarga = 0;
            foreach ($request->items as $item) {
                $totalHarga += $item['subtotal'] ?? 0;
            }

            // Jika action langsung beli, pastikan saldo cukup sebelum insert
            if ($request->action === 'langsung_beli') {
                if ($request->metode_pembayaran === 'cash_pusat') {
                    $kasPusat = KasPusat::lockForUpdate()->firstOrCreate([], ['saldo' => 0]);
                    if ($kasPusat->saldo < $totalHarga) {
                        throw new \Exception("Saldo Kas Pusat tidak mencukupi. Saldo: Rp " . number_format($kasPusat->saldo, 0, ',', '.'));
                    }
                    $kasPusat->decrement('saldo', $totalHarga);
                } else {
                    $dompetPusat = \App\Models\PusatAplikasiLayanan::lockForUpdate()->find($request->apk_layanan_id);
                    if (!$dompetPusat || $dompetPusat->saldo_sekarang < $totalHarga) {
                        throw new \Exception("Saldo Dompet Digital Pusat tidak mencukupi.");
                    }
                    $dompetPusat->decrement('saldo_sekarang', $totalHarga);
                    
                    \App\Services\RiwayatApkService::catat(
                        'pusat', 
                        $dompetPusat->id, 
                        'keluar', 
                        $totalHarga, 
                        'Pembelian ke Supplier', 
                        null
                    );
                }
            }

            $pembelian = PembelianSupplier::create([
                'admin_id'    => Auth::id(),
                'supplier_id' => $supplier->id,
                'total_harga' => $totalHarga,
                'status'      => $request->action === 'langsung_beli' ? 'selesai' : 'draft',
                'metode_pembayaran' => $request->metode_pembayaran ?? 'cash_pusat',
                'apk_layanan_id'    => $request->action === 'langsung_beli' && $request->metode_pembayaran === 'apk_layanan' ? $request->apk_layanan_id : null,
                'tanggal_pembelian' => now()->toDateString(),
            ]);

            foreach ($request->items as $item) {
                $subtotal = $item['subtotal'] ?? 0;
                $hargaSatuan = $subtotal / max(1, $item['qty']);
                
                $detail = DetailPembelian::create([
                    'pembelian_id'    => $pembelian->id,
                    'barang_fisik_id' => $item['barang_fisik_id'],
                    'qty'             => $item['qty'],
                    'harga_satuan'    => round($hargaSatuan, 2),
                    'subtotal'        => $subtotal,
                ]);

                // Jika action langsung beli, otomatis terima barang fisik ke inventori
                if ($request->action === 'langsung_beli') {
                    $kodeBatch = "BATCH-PO-{$pembelian->id}-" . date('Ymd', strtotime($pembelian->tanggal_pembelian));
                    $inventoryService->terimaBarangMasuk([
                        'pembelian_id'     => $pembelian->id,
                        'supplier_id'      => $pembelian->supplier_id,
                        'barang_fisik_id'  => $item['barang_fisik_id'],
                        'qty_beli'         => $item['qty'],
                        'total_harga_beli' => $subtotal,
                        'kode_batch'       => $kodeBatch,
                    ]);
                }
            }

            // Jika dibuat untuk menindaklanjuti Request Operasional Cabang
            if ($request->request_id) {
                $reqOp = \App\Models\RequestOperasional::find($request->request_id);
                if ($reqOp) {
                    $catatan = "Dipesankan ke Supplier via PO-" . str_pad($pembelian->id, 5, '0', STR_PAD_LEFT);
                    $reqOp->catatan = $reqOp->catatan ? ($reqOp->catatan . " | " . $catatan) : $catatan;
                    $reqOp->save();
                }
            }

            DB::commit();

            if ($request->action === 'langsung_beli') {
                return redirect()->route('admin.pembelian.index')->with('success', 'Pembelian berhasil diselesaikan, Saldo dipotong & Stok masuk gudang pusat.');
            }
            return redirect()->route('admin.pembelian.index')->with('success', 'Draft pembelian berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Tampilkan form edit draft
     */
    public function edit($id)
    {
        $pembelian = PembelianSupplier::with(['supplier', 'detailPembelian.barangFisik'])->findOrFail($id);
        
        if ($pembelian->status !== 'draft') {
            return redirect()->route('admin.pembelian.index')->with('error', 'Hanya PO dengan status draft yang dapat diedit.');
        }

        $barangFisik = \App\Models\MasterBarang::whereIn('tipe_barang', ['Fisik ACC', 'Fisik Voucher Kartu'])->get();
        $dompetPusat = \App\Models\PusatAplikasiLayanan::all();
        
        return view('admin.pembelian.edit', compact('pembelian', 'barangFisik', 'dompetPusat'));
    }

    /**
     * Update draft atau jadikan langsung beli
     */
    public function update(Request $request, $id, InventoryService $inventoryService)
    {
        $pembelian = PembelianSupplier::findOrFail($id);
        
        if ($pembelian->status !== 'draft') {
            return redirect()->route('admin.pembelian.index')->with('error', 'Hanya PO dengan status draft yang dapat diedit.');
        }

        $request->validate([
            'action'          => 'required|in:draft,langsung_beli',
            'nama_supplier'   => 'nullable|string|max:255',
            'kontak_supplier' => 'nullable|string|max:255',
            'items'           => 'required|array|min:1',
            'items.*.barang_fisik_id' => 'required|exists:master_barang,id',
            'items.*.qty'     => 'required|integer|min:1',
            'items.*.subtotal'=> $request->action === 'draft' ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'metode_pembayaran' => 'required_if:action,langsung_beli|in:cash_pusat,apk_layanan',
            'apk_layanan_id'    => 'nullable|required_if:metode_pembayaran,apk_layanan|exists:pusat_aplikasi_layanan,id',
        ]);

        $namaSupplier = $request->nama_supplier ?: 'Umum';
        $supplier = \App\Models\Supplier::firstOrCreate(
            ['nama_supplier' => $namaSupplier],
            ['kontak' => $request->kontak_supplier ?? '-']
        );

        DB::beginTransaction();
        try {
            $totalHarga = 0;
            foreach ($request->items as $item) {
                $totalHarga += $item['subtotal'] ?? 0;
            }

            if ($request->action === 'langsung_beli') {
                if ($request->metode_pembayaran === 'cash_pusat') {
                    $kasPusat = KasPusat::lockForUpdate()->firstOrCreate([], ['saldo' => 0]);
                    if ($kasPusat->saldo < $totalHarga) {
                        throw new \Exception("Saldo Kas Pusat tidak mencukupi. Saldo: Rp " . number_format($kasPusat->saldo, 0, ',', '.'));
                    }
                    $kasPusat->decrement('saldo', $totalHarga);
                } else {
                    $dompetPusat = \App\Models\PusatAplikasiLayanan::lockForUpdate()->find($request->apk_layanan_id);
                    if (!$dompetPusat || $dompetPusat->saldo_sekarang < $totalHarga) {
                        throw new \Exception("Saldo Dompet Digital Pusat tidak mencukupi.");
                    }
                    $dompetPusat->decrement('saldo_sekarang', $totalHarga);
                    
                    \App\Services\RiwayatApkService::catat(
                        'pusat', 
                        $dompetPusat->id, 
                        'keluar', 
                        $totalHarga, 
                        'Pembelian ke Supplier (PO #' . $pembelian->id . ')', 
                        null
                    );
                }
            }

            // Update PO
            $pembelian->update([
                'supplier_id' => $supplier->id,
                'total_harga' => $totalHarga,
                'status'      => $request->action === 'langsung_beli' ? 'selesai' : 'draft',
                'metode_pembayaran' => $request->metode_pembayaran ?? 'cash_pusat',
                'apk_layanan_id'    => $request->action === 'langsung_beli' && $request->metode_pembayaran === 'apk_layanan' ? $request->apk_layanan_id : null,
            ]);

            // Delete old details
            DetailPembelian::where('pembelian_id', $pembelian->id)->delete();

            // Recreate details
            foreach ($request->items as $item) {
                $subtotal = $item['subtotal'] ?? 0;
                $hargaSatuan = $subtotal / max(1, $item['qty']);
                
                $detail = DetailPembelian::create([
                    'pembelian_id'    => $pembelian->id,
                    'barang_fisik_id' => $item['barang_fisik_id'],
                    'qty'             => $item['qty'],
                    'harga_satuan'    => round($hargaSatuan, 2),
                    'subtotal'        => $subtotal,
                ]);

                if ($request->action === 'langsung_beli') {
                    $kodeBatch = "BATCH-PO-{$pembelian->id}-" . date('Ymd', strtotime($pembelian->tanggal_pembelian));
                    $inventoryService->terimaBarangMasuk([
                        'pembelian_id'     => $pembelian->id,
                        'supplier_id'      => $pembelian->supplier_id,
                        'barang_fisik_id'  => $item['barang_fisik_id'],
                        'qty_beli'         => $item['qty'],
                        'total_harga_beli' => $subtotal,
                        'kode_batch'       => $kodeBatch,
                    ]);
                }
            }

            DB::commit();

            if ($request->action === 'langsung_beli') {
                return redirect()->route('admin.pembelian.index')->with('success', 'Draft berhasil diselesaikan, Saldo dipotong & Stok masuk gudang pusat.');
            }
            return redirect()->route('admin.pembelian.index')->with('success', 'Draft pembelian berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Submit draft langsung menjadi "Selesai" (Langsung Beli).
     */
    public function submitDalamPerjalanan(Request $request, $id, InventoryService $inventoryService)
    {
        $request->validate([
            'metode_pembayaran' => 'required|in:cash_pusat,apk_layanan',
            'apk_layanan_id'    => 'nullable|required_if:metode_pembayaran,apk_layanan|exists:pusat_aplikasi_layanan,id',
        ]);

        DB::beginTransaction();
        try {
            $pembelian = PembelianSupplier::with('detailPembelian')->where('status', 'draft')->lockForUpdate()->findOrFail($id);

            // Potong Saldo
            if ($request->metode_pembayaran === 'cash_pusat') {
                $kasPusat = KasPusat::lockForUpdate()->firstOrCreate([], ['saldo' => 0]);
                if ($kasPusat->saldo < $pembelian->total_harga) {
                    throw new \Exception("Saldo Kas Pusat tidak mencukupi. Saldo saat ini: Rp " . number_format($kasPusat->saldo, 0, ',', '.'));
                }
                $kasPusat->decrement('saldo', $pembelian->total_harga);
            } else {
                $dompetPusat = \App\Models\PusatAplikasiLayanan::lockForUpdate()->find($request->apk_layanan_id);

                if (!$dompetPusat || $dompetPusat->saldo_sekarang < $pembelian->total_harga) {
                    throw new \Exception("Saldo Dompet Digital Pusat tidak mencukupi.");
                }
                $dompetPusat->decrement('saldo_sekarang', $pembelian->total_harga);

                \App\Services\RiwayatApkService::catat(
                    'pusat', 
                    $dompetPusat->id, 
                    'keluar', 
                    $pembelian->total_harga, 
                    'Penerimaan Pembelian PO #' . $pembelian->id, 
                    $pembelian
                );
            }

            // Terima Barang Masuk ke Gudang Pusat
            foreach ($pembelian->detailPembelian as $detail) {
                $kodeBatch = "BATCH-PO-{$pembelian->id}-" . date('Ymd', strtotime($pembelian->tanggal_pembelian));

                $inventoryService->terimaBarangMasuk([
                    'pembelian_id'     => $pembelian->id,
                    'supplier_id'      => $pembelian->supplier_id,
                    'barang_fisik_id'  => $detail->barang_fisik_id,
                    'qty_beli'         => $detail->qty,
                    'total_harga_beli' => $detail->subtotal,
                    'kode_batch'       => $kodeBatch,
                ]);
            }

            $pembelian->update([
                'status'            => 'selesai',
                'metode_pembayaran' => $request->metode_pembayaran,
                'apk_layanan_id'    => $request->metode_pembayaran === 'apk_layanan' ? $request->apk_layanan_id : null,
            ]);

            DB::commit();
            return back()->with('success', 'Pembelian berhasil dibayar. Stok otomatis masuk gudang pusat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function historis(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = PembelianSupplier::with(['supplier', 'admin', 'detailPembelian.barangFisik'])
            ->orderBy('tanggal_pembelian', 'desc');

        if ($startDate) {
            $query->whereDate('tanggal_pembelian', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('tanggal_pembelian', '<=', $endDate);
        }

        $pembelians = $query->paginate(50);
        return view('admin.pembelian.historis', compact('pembelians', 'startDate', 'endDate'));
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = PembelianSupplier::with(['supplier', 'admin', 'detailPembelian.barangFisik'])
            ->orderBy('tanggal_pembelian', 'desc');

        if ($startDate) $query->whereDate('tanggal_pembelian', '>=', $startDate);
        if ($endDate) $query->whereDate('tanggal_pembelian', '<=', $endDate);

        $pembelians = $query->get();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.pembelian.historis_pdf', compact('pembelians', 'startDate', 'endDate'));
        return $pdf->download('historis_pembelian_supplier_' . date('Ymd_His') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\PembelianExport($startDate, $endDate), 'historis_pembelian_supplier_' . date('Ymd_His') . '.xlsx');
    }
}
