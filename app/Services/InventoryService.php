<?php

namespace App\Services;

use App\Models\BarangMasuk;
use App\Models\MasterBarang;
use App\Models\StokPusat;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Proses penerimaan barang masuk dari supplier.
     *
     * Saat Admin Pusat menginput barang masuk, sistem otomatis:
     * 1. Menghitung harga modal per pcs (total_harga_beli / qty_beli)
     * 2. Menyimpan data barang masuk
     * 3. Menambah stok pusat (gudang)
     *
     * @param array $data [supplier_id, barang_fisik_id, qty_beli, total_harga_beli, kode_batch]
     * @return array ['barang_masuk' => BarangMasuk, 'harga_modal_per_pcs' => float, 'harga_modal_rata_rata' => float]
     */
    public function terimaBarangMasuk(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // Dapatkan Master Barang
            $masterBarang = MasterBarang::findOrFail($data['barang_fisik_id']);
            
            // 1. Hitung harga modal (HPP) per pcs untuk batch ini
            $hargaModalPerPcs = $data['total_harga_beli'] / $data['qty_beli'];
            
            // 2. Otomatisasi 2 (Validasi HPP vs Harga Jual)
            if ($hargaModalPerPcs >= $masterBarang->harga_jual) {
                throw new \Exception("Harga Modal melebihi Harga Jual untuk barang {$masterBarang->nama_barang}, silakan update Harga Jual di Master Barang terlebih dahulu");
            }
            
            // 3. Otomatisasi 3 (Logika Auto-Batas Diskon berdasarkan Kategori)
            $batasDiskon = 0;
            if ($masterBarang->tipe_barang === 'Fisik ACC') {
                $batasDiskon = $hargaModalPerPcs + ($hargaModalPerPcs * 0.10);
            } else if ($masterBarang->tipe_barang === 'Fisik Voucher Kartu') {
                $batasDiskon = 0;
            }

            // 4. Simpan data barang masuk
            $barangMasuk = BarangMasuk::create([
                'pembelian_id'     => $data['pembelian_id'] ?? null,
                'supplier_id'      => $data['supplier_id'],
                'barang_fisik_id'  => $data['barang_fisik_id'],
                'qty_beli'         => $data['qty_beli'],
                'stok_sisa'        => $data['qty_beli'], // Untuk logika FIFO
                'total_harga_beli' => $data['total_harga_beli'],
                'batas_diskon'     => $batasDiskon,
                'kode_batch'       => $data['kode_batch'],
            ]);

            // 3. Tambah stok pusat (gudang)
            $stokPusat = StokPusat::firstOrCreate(
                ['barang_id' => $data['barang_fisik_id']],
                ['jumlah_stok' => 0]
            );
            $stokPusat->increment('jumlah_stok', $data['qty_beli']);

            // 4. Hitung Harga Modal Rata-rata dari seluruh barang masuk
            $hargaModalRataRata = $this->hitungHargaModalRataRata($data['barang_fisik_id']);

            return [
                'barang_masuk'          => $barangMasuk,
                'harga_modal_per_pcs'   => round($hargaModalPerPcs, 2),
                'harga_modal_rata_rata' => $hargaModalRataRata,
            ];
        });
    }

    /**
     * Hitung Harga Modal Rata-rata (Weighted Average) dari seluruh stok barang di gudang.
     *
     * Rumus: Total Harga Beli Semua Batch / Total Qty Semua Batch
     *
     * Menggunakan Weighted Average karena pembelian di masa depan bisa berbeda harganya.
     * Contoh:
     *   - Batch 1: 200 pcs x Rp 1.500 = Rp 300.000
     *   - Batch 2: 100 pcs x Rp 2.000 = Rp 200.000
     *   - Rata-rata = (300.000 + 200.000) / (200 + 100) = Rp 1.666,67/pcs
     *
     * @param int $barangId ID dari master_barang
     * @return float Harga modal rata-rata per pcs
     */
    public function hitungHargaModalRataRata(int $barangId): float
    {
        $aggregasi = BarangMasuk::where('barang_fisik_id', $barangId)
            ->selectRaw('SUM(total_harga_beli) as total_harga, SUM(qty_beli) as total_qty')
            ->first();

        // Jika belum ada data barang masuk, return 0
        if (!$aggregasi || $aggregasi->total_qty == 0) {
            return 0;
        }

        return round($aggregasi->total_harga / $aggregasi->total_qty, 2);
    }

    /**
     * Ambil ringkasan inventory untuk suatu barang fisik.
     *
     * @param int $barangId ID dari master_barang
     * @return array Informasi lengkap: harga jual, modal rata-rata, total stok, margin
     */
    public function getRingkasanBarang(int $barangId): array
    {
        $barang = MasterBarang::findOrFail($barangId);
        $hargaModalRataRata = $this->hitungHargaModalRataRata($barangId);
        $stokPusat = StokPusat::where('barang_id', $barangId)->first();

        return [
            'barang'                => $barang,
            'harga_jual'            => (float) $barang->harga_jual,
            'harga_modal_rata_rata' => $hargaModalRataRata,
            'margin'                => $hargaModalRataRata > 0
                                        ? round($barang->harga_jual - $hargaModalRataRata, 2)
                                        : null,
            'total_stok_gudang'     => $stokPusat ? $stokPusat->jumlah_stok : 0,
        ];
    }
}
