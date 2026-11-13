<?php

namespace App\Imports;

use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\MasterBarang;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class TransaksiHistorisImport implements ToModel, WithHeadingRow
{
    protected $kasirId;

    public function __construct($kasirId)
    {
        $this->kasirId = $kasirId;
    }

    public function model(array $row)
    {
        // Minimal butuh cabang_id, barang_id, kuantitas, tanggal_transaksi
        if (!isset($row['tanggal_transaksi']) || !isset($row['cabang_id']) || !isset($row['barang_id']) || !isset($row['kuantitas'])) {
            return null;
        }

        $tanggal = Carbon::parse($row['tanggal_transaksi'])->format('Y-m-d') . ' 12:00:00';
        $qty = $row['kuantitas'];
        
        $barang = MasterBarang::find($row['barang_id']);
        $hargaJual = $barang ? $barang->harga_jual : 0;
        
        $oldestBarangMasuk = \App\Models\BarangMasuk::where('barang_fisik_id', $row['barang_id'])
                                ->orderBy('created_at', 'asc')
                                ->first();
                                
        $hppSatuan = 0;
        if ($oldestBarangMasuk && $oldestBarangMasuk->qty_beli > 0) {
            $hppSatuan = $oldestBarangMasuk->total_harga_beli / $oldestBarangMasuk->qty_beli;
        }

        $totalTagihan = $qty * $hargaJual;

        // 1. Insert Header Transaksi
        $trx = Transaksi::create([
            'cabang_id' => $row['cabang_id'],
            'kasir_id' => $this->kasirId,
            'metode_pembayaran' => 'cash',
            'total_tagihan' => $totalTagihan,
            'uang_diterima' => $totalTagihan,
            'kembalian' => 0,
            'fee_transaksi' => 0,
            'status' => 'success',
            'tanggal_transaksi' => $tanggal,
            'created_at' => $tanggal,
            'updated_at' => $tanggal,
        ]);

        // 2. Insert Detail Transaksi
        DetailTransaksi::create([
            'transaksi_id' => $trx->id,
            'barang_id' => $row['barang_id'],
            'kuantitas' => $qty,
            'harga_satuan' => $hargaJual,
            'subtotal' => $totalTagihan,
            'hpp_satuan' => $hppSatuan,
            'created_at' => $tanggal,
            'updated_at' => $tanggal,
        ]);

        return null; // Return null karena kita save manual
    }
}
