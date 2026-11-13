<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StokCabangBatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stokCabangs = \App\Models\StokCabang::where('jumlah_stok', '>', 0)->get();
        $inventoryService = new \App\Services\InventoryService();

        foreach ($stokCabangs as $stok) {
            // Find or create a dummy BarangMasuk for this item if none exists
            $barangMasuk = \App\Models\BarangMasuk::where('barang_fisik_id', $stok->barang_id)->first();
            
            if (!$barangMasuk) {
                // Create a dummy supplier if none exists
                $supplier = \App\Models\Supplier::firstOrCreate(
                    ['nama_supplier' => 'Sistem Migrasi'],
                    ['kontak' => '-']
                );

                $masterBarang = \App\Models\MasterBarang::find($stok->barang_id);
                $hpp = $masterBarang ? ($masterBarang->harga_jual * 0.8) : 1000; // default HPP 80% of selling price

                $barangMasuk = \App\Models\BarangMasuk::create([
                    'supplier_id' => $supplier->id,
                    'barang_fisik_id' => $stok->barang_id,
                    'qty_beli' => $stok->jumlah_stok,
                    'stok_sisa' => 0, // already at branch
                    'total_harga_beli' => $stok->jumlah_stok * $hpp,
                    'kode_batch' => 'BATCH-MIGRASI-' . $stok->barang_id
                ]);
            }

            // Hitung harga modal
            $hargaModalPerPcs = $barangMasuk->total_harga_beli / max(1, $barangMasuk->qty_beli);

            \App\Models\StokCabangBatch::firstOrCreate([
                'cabang_id' => $stok->cabang_id,
                'barang_id' => $stok->barang_id,
                'barang_masuk_id' => $barangMasuk->id,
            ], [
                'stok_sisa_cabang' => $stok->jumlah_stok,
                'hpp_batch' => $hargaModalPerPcs
            ]);
        }
    }
}
