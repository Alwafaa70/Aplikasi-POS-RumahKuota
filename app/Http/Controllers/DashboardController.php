<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cabang;
use App\Models\MasterBarang;
use App\Models\StokPusat;
use App\Models\StokCabang;
use App\Models\KasPusat;
use App\Models\KasCabang;
use App\Models\SaldoApkLayanan;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman Monitoring Aset (Dashboard Admin Pusat).
     */
    public function monitoring(Request $request, InventoryService $inventoryService)
    {
        $lokasi = $request->query('lokasi', 'all'); // 'all', 'pusat', atau id cabang
        $kategori = $request->query('kategori', 'all'); // 'all', 'barang_fisik', 'cash', 'saldo_digital'

        $cabangList = Cabang::all();

        // 1. Inisialisasi variabel metrics
        $totalQtyFisik = 0;
        $totalNilaiFisikModal = 0;
        $totalNilaiFisikJual = 0;

        $totalCash = 0;
        
        $totalSaldoDigital = 0;

        // --- MENGHITUNG BARANG FISIK ---
        if ($kategori === 'all' || $kategori === 'barang_fisik') {
            $masterBarang = MasterBarang::all();
            
            foreach ($masterBarang as $barang) {
                $qtyBarangIni = 0;
                
                // Cari stok pusat
                if ($lokasi === 'all' || $lokasi === 'pusat') {
                    $stokPusat = StokPusat::where('barang_id', $barang->id)->value('jumlah_stok') ?? 0;
                    $qtyBarangIni += $stokPusat;
                }

                // Cari stok cabang
                if ($lokasi === 'all') {
                    $stokCabang = StokCabang::where('barang_id', $barang->id)->sum('jumlah_stok');
                    $qtyBarangIni += $stokCabang;
                } elseif (is_numeric($lokasi)) {
                    $stokCabang = StokCabang::where('cabang_id', $lokasi)
                                            ->where('barang_id', $barang->id)
                                            ->value('jumlah_stok') ?? 0;
                    $qtyBarangIni += $stokCabang;
                }

                if ($qtyBarangIni > 0) {
                    $hpp = $inventoryService->hitungHargaModalRataRata($barang->id);
                    $totalQtyFisik += $qtyBarangIni;
                    $totalNilaiFisikModal += ($qtyBarangIni * $hpp);
                    $totalNilaiFisikJual += ($qtyBarangIni * $barang->harga_jual);
                }
            }
        }

        // --- MENGHITUNG CASH FISIK ---
        if ($kategori === 'all' || $kategori === 'cash') {
            if ($lokasi === 'all' || $lokasi === 'pusat') {
                $totalCash += KasPusat::sum('saldo');
            }

            if ($lokasi === 'all') {
                $totalCash += KasCabang::sum('saldo');
            } elseif (is_numeric($lokasi)) {
                $totalCash += KasCabang::where('cabang_id', $lokasi)->sum('saldo');
            }
        }

        // --- MENGHITUNG SALDO DIGITAL ---
        if ($kategori === 'all' || $kategori === 'saldo_digital') {
            if ($lokasi === 'all' || $lokasi === 'pusat') {
                $totalSaldoDigital += SaldoApkLayanan::whereNull('cabang_id')->sum('saldo');
            }

            if ($lokasi === 'all') {
                $totalSaldoDigital += SaldoApkLayanan::whereNotNull('cabang_id')->sum('saldo');
            } elseif (is_numeric($lokasi)) {
                $totalSaldoDigital += SaldoApkLayanan::where('cabang_id', $lokasi)->sum('saldo');
            }
        }

        // 4. Perhitungan Total Keseluruhan
        $totalAsetModal = $totalNilaiFisikModal + $totalCash + $totalSaldoDigital;
        $totalAsetJual  = $totalNilaiFisikJual + $totalCash + $totalSaldoDigital;

        return view('admin.dashboard.monitoring', compact(
            'cabangList',
            'lokasi',
            'kategori',
            'totalQtyFisik',
            'totalNilaiFisikModal',
            'totalNilaiFisikJual',
            'totalCash',
            'totalSaldoDigital',
            'totalAsetModal',
            'totalAsetJual'
        ));
    }
}
