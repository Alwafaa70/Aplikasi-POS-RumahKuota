<?php

namespace App\Services;

use App\Models\MasterBarang;
use App\Models\Cabang;
use App\Models\PengaturanEws;
use App\Models\StokCabang;
use App\Models\BarangMasuk;
use App\Models\DetailTransaksi;
use Illuminate\Support\Carbon;

class EwsService
{
    protected $defaultLeadTime = 7; // Hari siklus pemesanan logistik ritel

    /**
     * Hitung atau ambil data lengkap EWS untuk satu item dan lokasi tertentu
     * 
     * @param int $barangId
     * @param int|null $cabangId (null = Gudang Pusat)
     * @param int $targetHariRestok (maksimal 30 hari)
     * @return array
     */
    public function getItemEwsDetail($barangId, $cabangId = null, $targetHariRestok = 30)
    {
        $barang = MasterBarang::with('category')->find($barangId);
        if (!$barang) return null;

        // Kunci target hari maksimal 30 hari (prinsip short-term SES)
        $targetHari = max(1, min(30, (int) $targetHariRestok));

        // Ambil pengaturan yang tersimpan
        $pengaturan = PengaturanEws::where('barang_id', $barangId)
            ->where('cabang_id', $cabangId)
            ->first();

        // 1. Hitung Stok Saat Ini
        if ($cabangId === null) {
            $stokSaatIni = (int) BarangMasuk::where('barang_fisik_id', $barangId)->sum('stok_sisa');
            $lokasiNama = 'Gudang Pusat';
            $tipeLokasi = 'Gudang Pusat';
        } else {
            $stokRow = StokCabang::where('barang_id', $barangId)->where('cabang_id', $cabangId)->first();
            $stokSaatIni = $stokRow ? (int) $stokRow->jumlah_stok : 0;
            $cabang = Cabang::find($cabangId);
            $lokasiNama = $cabang ? $cabang->nama_cabang : 'Cabang #' . $cabangId;
            $tipeLokasi = 'Cabang';
        }

        // 2. Ambil atau estimasi Tren Penjualan Harian
        if ($pengaturan && $pengaturan->daily_forecast !== null) {
            $dailyForecast = (float) $pengaturan->daily_forecast;
        } else {
            $dailyForecast = $this->calculateFallbackDailyDemand($barangId, $cabangId);
        }

        $leadTime = $pengaturan ? (int) $pengaturan->lead_time : $this->defaultLeadTime;
        
        // 3. Batas EWS = Tren Harian * Lead Time (minimal 1 unit)
        $batasEws = $pengaturan && $pengaturan->batas_ews 
            ? (int) $pengaturan->batas_ews 
            : max(1, (int) ceil($dailyForecast * $leadTime));

        // 4. Klasifikasi Kecepatan Perputaran (FSN Analysis) & Penentuan Safety Stock
        $classification = $this->classifyProductSpeed($dailyForecast, $cabangId === null);
        $safetyPercent = $pengaturan && $pengaturan->safety_stock_percent 
            ? (int) $pengaturan->safety_stock_percent 
            : $classification['percent'];

        // 5. Perhitungan Kebutuhan dan Rekomendasi Restok
        $demandForecast = round($dailyForecast * $targetHari, 2);
        $safetyStockUnits = (int) ceil($demandForecast * ($safetyPercent / 100));
        $totalKebutuhan = (int) ceil($demandForecast + $safetyStockUnits);
        $rekomendasiRestok = max(0, $totalKebutuhan - $stokSaatIni);
        $isKritis = ($stokSaatIni <= $batasEws);

        return [
            'barang_id' => $barang->id,
            'nama_barang' => $barang->nama_barang,
            'kategori' => $barang->category ? $barang->category->nama_kategori : 'Umum',
            'tipe_barang' => $barang->tipe_barang,
            'cabang_id' => $cabangId,
            'lokasi_nama' => $lokasiNama,
            'tipe_lokasi' => $tipeLokasi,
            'stok_saat_ini' => $stokSaatIni,
            'daily_forecast' => $dailyForecast,
            'lead_time' => $leadTime,
            'batas_ews' => $batasEws,
            'is_kritis' => $isKritis,
            'target_hari' => $targetHari,
            'demand_forecast' => $demandForecast,
            'safety_percent' => $safetyPercent,
            'safety_stock_units' => $safetyStockUnits,
            'rekomendasi_restok' => $rekomendasiRestok,
            'klasifikasi_nama' => $classification['label'],
            'klasifikasi_badge' => $classification['badge'],
            'alasan_ilmiah' => $classification['reason'],
            'rumus_batas' => "Tren Harian ({$dailyForecast}) × Lead Time ({$leadTime} Hari) = {$batasEws} Pcs",
            'rumus_restok' => "(Kebutuhan {$targetHari} Hari [{$demandForecast}] + Cadangan Safety [{$safetyStockUnits}]) - Sisa Stok [{$stokSaatIni}] = {$rekomendasiRestok} Pcs"
        ];
    }

    /**
     * Klasifikasi FSN Analysis (Fast, Medium, Slow) berdasarkan kecepatan tren harian
     */
    public function classifyProductSpeed($dailyForecast, $isGudangPusat = false)
    {
        if ($isGudangPusat) {
            // Skala agregat seluruh cabang untuk gudang pusat
            if ($dailyForecast >= 20.0) {
                return [
                    'label' => 'Fast-Moving (Sangat Laris)',
                    'badge' => 'fast',
                    'percent' => 30,
                    'reason' => 'Tren penjualan sangat tinggi sebagai penyumbang omset utama. Cadangan buffer 30% diterapkan untuk menahan lonjakan pembeli mendadak dan mencegah kerugian akibat kehabisan stok (stockout).'
                ];
            } elseif ($dailyForecast >= 10.0) {
                return [
                    'label' => 'Medium-Moving (Stabil)',
                    'badge' => 'medium',
                    'percent' => 20,
                    'reason' => 'Tren penjualan tergolong stabil dan berpola normal. Buffer 20% merupakan proporsi ideal standar operasional ritel untuk menyerap fluktuasi tanpa menimbun modal kas berlebih.'
                ];
            } else {
                return [
                    'label' => 'Slow-Moving (Kurang Laris)',
                    'badge' => 'slow',
                    'percent' => 10,
                    'reason' => 'Tren penjualan lambat/jarang laku. Cadangan sengaja ditekan 10% agar modal kas tidak tertimbun (dead capital) serta menghindari resiko barang rusak, berdebu, atau masa aktif voucher hangus.'
                ];
            }
        } else {
            // Skala level cabang toko perorangan
            if ($dailyForecast >= 2.5) {
                return [
                    'label' => 'Fast-Moving (Sangat Laris)',
                    'badge' => 'fast',
                    'percent' => 30,
                    'reason' => 'Tren penjualan di cabang ini sangat tinggi. Buffer 30% disiapkan untuk mengantisipasi lonjakan pembeli agar tidak kehilangan omset harian cabang.'
                ];
            } elseif ($dailyForecast >= 1.0) {
                return [
                    'label' => 'Medium-Moving (Stabil)',
                    'badge' => 'medium',
                    'percent' => 20,
                    'reason' => 'Tren penjualan di cabang stabil. Buffer 20% menjadi cadangan pengaman standar.'
                ];
            } else {
                return [
                    'label' => 'Slow-Moving (Kurang Laris)',
                    'badge' => 'slow',
                    'percent' => 10,
                    'reason' => 'Barang kurang laku di cabang ini. Buffer ditekan minimal 10% agar laci/rak cabang tidak sesak dan modal tidak membeku.'
                ];
            }
        }
    }

    /**
     * Hitung rata-rata penjualan per hari dari data transaksi nyata
     */
    protected function calculateFallbackDailyDemand($barangId, $cabangId = null)
    {
        $query = DetailTransaksi::join('transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')
            ->where('detail_transaksi.barang_id', $barangId);

        if ($cabangId !== null) {
            $query->where('transaksi.cabang_id', $cabangId);
        }

        $totalSold = (float) $query->sum('detail_transaksi.kuantitas');
        $earliestDate = (clone $query)->min('transaksi.tanggal_transaksi');

        if ($earliestDate && $totalSold > 0) {
            $diffDays = max(1, abs(Carbon::parse($earliestDate)->diffInDays(Carbon::now())));
            return max(0.1, round($totalSold / $diffDays, 2));
        }

        return 0.5; // Estimasi default jika belum ada riwayat penjualan sama sekali
    }

    /**
     * Ambil seluruh item kritis EWS untuk dashboard monitoring
     */
    public function getAllKritisItems($targetHari = 30)
    {
        $barangs = MasterBarang::whereIn('tipe_barang', ['Fisik ACC', 'Fisik Voucher Kartu'])->get();
        $cabangs = Cabang::all();

        $kritisList = [];

        // 1. Cek Gudang Pusat
        foreach ($barangs as $barang) {
            $detail = $this->getItemEwsDetail($barang->id, null, $targetHari);
            if ($detail && $detail['is_kritis']) {
                $kritisList[] = $detail;
            }
        }

        // 2. Cek Tiap Cabang
        foreach ($cabangs as $cabang) {
            foreach ($barangs as $barang) {
                $detail = $this->getItemEwsDetail($barang->id, $cabang->id, $targetHari);
                if ($detail && $detail['is_kritis']) {
                    $kritisList[] = $detail;
                }
            }
        }

        return $kritisList;
    }
}
