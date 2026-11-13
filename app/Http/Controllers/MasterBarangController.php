<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterBarang;
use App\Models\BarangMasuk;
use Illuminate\Support\Facades\DB;

class MasterBarangController extends Controller
{
    public function index()
    {
        $barangs = MasterBarang::with(['category.parent.parent', 'rangesDigitalB'])->get();
        // Load hierarchical categories as JSON for Alpine.js
        $categoriesTree = \App\Models\Category::whereNull('parent_id')->with('children.children')->get();
        return view('master_barang.index', compact('barangs', 'categoriesTree'));
    }

    public function storeKategori(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'tipe_barang_terkait' => 'required|in:Fisik ACC,Fisik Voucher Kartu,Digital A,Digital B',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        // Cegah pembuatan sub-kategori lebih dari 3 level (Parent -> Sub 1 -> Sub 2)
        if ($request->parent_id) {
            $parent = \App\Models\Category::find($request->parent_id);
            if ($parent && $parent->parent_id !== null) {
                // $parent is already a sub-category. Let's check its parent.
                $grandParent = \App\Models\Category::find($parent->parent_id);
                if ($grandParent && $grandParent->parent_id !== null) {
                    return back()->with('error', 'Tidak dapat membuat sub-kategori lebih dari 3 level (Utama -> Sub 1 -> Sub 2).');
                }
            }
        }

        \App\Models\Category::create([
            'name' => $request->name,
            'tipe_barang_terkait' => $request->tipe_barang_terkait,
            'parent_id' => $request->parent_id ?: null
        ]);

        return back()->with('success', 'Kategori berhasil ditambahkan')
                     ->with('keep_modal_open', 'kategori')
                     ->with('last_tipe_barang_kategori', $request->tipe_barang_terkait);
    }

    public function updateKategori(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        
        $category = \App\Models\Category::findOrFail($id);
        $category->name = $request->name;
        $category->save();
        
        return back()->with('success', 'Nama Kategori berhasil diperbarui');
    }

    public function destroyKategori($id)
    {
        $category = \App\Models\Category::findOrFail($id);
        
        // Cek jika kategori punya child
        if ($category->children()->count() > 0) {
            return back()->with('error', 'Gagal menghapus! Kategori ini memiliki sub-kategori.');
        }

        // Cek jika kategori dipakai di Master Barang
        if ($category->masterBarang()->count() > 0) {
            return back()->with('error', 'Gagal menghapus! Kategori ini sedang digunakan oleh Master Barang.');
        }

        $category->delete();
        return back()->with('success', 'Kategori berhasil dihapus');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'tipe_barang' => 'required|in:Fisik ACC,Fisik Voucher Kartu,Digital A,Digital B',
            'category_id' => 'nullable|exists:categories,id',
            'safety_stock_percent' => 'nullable|integer|min:0|max:100',
        ]);

        $tipeBarang = $request->tipe_barang;
        $categoryId = $request->category_id;
        $jenisTransaksi = $request->jenis_transaksi;
        
        DB::beginTransaction();
        try {
            $barang = new MasterBarang();
            $barang->nama_barang = $request->nama_barang;
            $barang->category_id = $categoryId;
            $barang->tipe_barang = $tipeBarang;
            $barang->jenis_transaksi = $jenisTransaksi; // 'cash_in' atau 'cash_out'
            
            // Harga jual dan safety stock hanya untuk selain Digital B
            if ($tipeBarang !== 'Digital B') {
                $barang->harga_jual = $request->harga_jual ?? 0;
            } else {
                $barang->harga_jual = 0;
            }

            if ($tipeBarang === 'Fisik ACC' || $tipeBarang === 'Fisik Voucher Kartu') {
                $barang->safety_stock_percent = $request->safety_stock_percent ?? 20;
            } else {
                $barang->safety_stock_percent = 0;
            }
            
            $barang->save();
            
            // Simpan ranges khusus Digital B
            if ($tipeBarang === 'Digital B' && $request->has('ranges')) {
                $ranges = json_decode($request->ranges, true);
                if(is_array($ranges)) {
                    foreach ($ranges as $range) {
                        DB::table('digital_b_ranges')->insert([
                            'master_barang_id' => $barang->id,
                            'min_nominal' => $range['min'],
                            'max_nominal' => $range['max'],
                            'fee' => $range['fee'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            return back()->with('success', 'Berhasil menambahkan Master Data')
                         ->with('keep_modal_open', 'barang')
                         ->with('last_tipe_barang', $tipeBarang);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                         ->with('keep_modal_open', 'barang')
                         ->with('last_tipe_barang', $tipeBarang);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'tipe_barang' => 'required|in:Fisik ACC,Fisik Voucher Kartu,Digital A,Digital B',
            'category_id' => 'nullable|exists:categories,id',
            'safety_stock_percent' => 'nullable|integer|min:0|max:100',
        ]);

        $barang = MasterBarang::findOrFail($id);
        $tipeBarang = $request->tipe_barang;
        $jenisTransaksi = $request->jenis_transaksi;
        
        DB::beginTransaction();
        try {
            $barang->nama_barang = $request->nama_barang;
            $barang->category_id = $request->category_id;
            $barang->tipe_barang = $tipeBarang;
            $barang->jenis_transaksi = $jenisTransaksi;
            
            if ($tipeBarang !== 'Digital B') {
                $barang->harga_jual = $request->harga_jual ?? 0;
            } else {
                $barang->harga_jual = 0;
            }

            if ($tipeBarang === 'Fisik ACC' || $tipeBarang === 'Fisik Voucher Kartu') {
                $barang->safety_stock_percent = $request->safety_stock_percent ?? 20;
            } else {
                $barang->safety_stock_percent = 0;
            }
            
            $barang->save();
            
            if ($tipeBarang === 'Digital B' && $request->has('ranges')) {
                // Hapus range lama, ganti yang baru
                DB::table('digital_b_ranges')->where('master_barang_id', $barang->id)->delete();
                
                $ranges = json_decode($request->ranges, true);
                if(is_array($ranges)) {
                    foreach ($ranges as $range) {
                        DB::table('digital_b_ranges')->insert([
                            'master_barang_id' => $barang->id,
                            'min_nominal' => $range['min'],
                            'max_nominal' => $range['max'],
                            'fee' => $range['fee'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            return back()->with('success', 'Master Barang berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    /**
     * Memperbarui batas maksimal diskon (max_diskon) untuk sebuah barang.
     */
    public function updateMaxDiskon(Request $request, $id)
    {
        $request->validate([
            'max_diskon' => 'nullable|numeric|min:0',
        ]);

        $barang = MasterBarang::findOrFail($id);

        // Aturan Voucher Kartu mutlak tidak memiliki kolom diskon
        if (stripos($barang->nama_barang, 'voucher kartu') !== false) {
            return back()->with('error', 'Barang tipe Voucher Kartu mutlak tidak boleh memiliki diskon.');
        }

        $maxDiskon = $request->max_diskon;

        if ($maxDiskon !== null && $maxDiskon > 0) {
            // Hitung HPP tertinggi yang ada di stok saat ini (stok_sisa > 0)
            $hppTertinggi = 0;
            
            $batchTersedia = BarangMasuk::where('barang_fisik_id', $id)
                                ->where('stok_sisa', '>', 0)
                                ->get();

            foreach ($batchTersedia as $batch) {
                // Harga modal per pcs dari batch ini
                if ($batch->qty_beli > 0) {
                    $hppBatch = $batch->total_harga_beli / $batch->qty_beli;
                    if ($hppBatch > $hppTertinggi) {
                        $hppTertinggi = $hppBatch;
                    }
                }
            }

            // Validasi: nominal diskon tidak boleh mengakibatkan harga lebih rendah dari HPP tertinggi
            // Maksudnya harga_jual - max_diskon >= HPP Tertinggi
            // Jadi max_diskon <= harga_jual - HPP Tertinggi
            $maxAllowedDiskon = $barang->harga_jual - $hppTertinggi;

            if ($maxDiskon > $maxAllowedDiskon) {
                return back()->with('error', "Diskon ditolak! Nominal diskon Rp " . number_format($maxDiskon, 0, ',', '.') . 
                                            " mengakibatkan harga jual di bawah Harga Pokok Penjualan (HPP) tertinggi saat ini (Rp " . number_format($hppTertinggi, 0, ',', '.') . "). " .
                                            "Maksimal diskon yang diizinkan adalah Rp " . number_format($maxAllowedDiskon, 0, ',', '.'));
            }
        }

        $barang->max_diskon = $maxDiskon;
        $barang->save();

        return back()->with('success', 'Batas maksimal diskon berhasil diperbarui.');
    }

    public function cetak(Request $request)
    {
        $tipe = $request->get('tipe');
        $kategori = $request->get('kategori');
        $search = $request->get('search');

        $query = MasterBarang::with(['category.parent.parent', 'rangesDigitalB']);

        if ($tipe && $tipe !== 'Semua') {
            $query->where('tipe_barang', $tipe);
        }

        if ($kategori) {
            $catIds = \App\Models\Category::where('id', $kategori)->orWhere('parent_id', $kategori)->pluck('id');
            $query->whereIn('category_id', $catIds);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_barang', 'like', '%' . $search . '%')
                  ->orWhere('id', 'like', '%' . $search . '%');
            });
        }

        $sort = $request->get('sort', 'id_desc');
        if ($sort === 'id_asc') {
            $query->orderBy('id', 'asc');
        } elseif ($sort === 'nama_asc') {
            $query->orderBy('nama_barang', 'asc');
        } elseif ($sort === 'tipe_barang') {
            $query->leftJoin('categories', 'master_barang.category_id', '=', 'categories.id')
                  ->orderBy('master_barang.tipe_barang', 'asc')
                  ->orderBy('categories.name', 'asc')
                  ->orderBy('master_barang.nama_barang', 'asc')
                  ->select('master_barang.*');
        } elseif ($sort === 'kategori') {
            // Join with categories for sorting by category name
            $query->leftJoin('categories', 'master_barang.category_id', '=', 'categories.id')
                  ->orderBy('categories.name', 'asc')
                  ->orderBy('master_barang.nama_barang', 'asc')
                  ->select('master_barang.*');
        } else {
            $query->orderBy('id', 'desc');
        }

        $barangs = $query->get();

        return view('master_barang.cetak', compact('barangs', 'tipe', 'kategori', 'search'));
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $barang = MasterBarang::findOrFail($id);
            
            // Delete associated ranges if Digital B
            if ($barang->tipe_barang === 'Digital B') {
                DB::table('digital_b_ranges')->where('master_barang_id', $barang->id)->delete();
            }

            $barang->delete();
            DB::commit();

            return back()->with('success', 'Barang berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            // Usually error code 23000 is integrity constraint violation (foreign key)
            if ($e->getCode() == 23000 || $e->getCode() == '23000') {
                return back()->with('error', 'Gagal menghapus: Barang ini sudah memiliki riwayat transaksi atau stok cabang yang terkait sehingga tidak bisa dihapus.');
            }
            return back()->with('error', 'Gagal menghapus barang: ' . $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus barang: ' . $e->getMessage());
        }
    }
}
