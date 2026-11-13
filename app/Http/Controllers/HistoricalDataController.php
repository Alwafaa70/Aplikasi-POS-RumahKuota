<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cabang;
use App\Models\MasterBarang;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TransaksiHistorisImport;

class HistoricalDataController extends Controller
{
    public function index()
    {
        $cabangs = Cabang::all();
        $barangs = MasterBarang::whereIn('tipe_barang', ['Fisik ACC', 'Fisik Voucher Kartu'])->get();
        return view('owner.historical_data.index', compact('cabangs', 'barangs'));
    }

    public function downloadTemplate()
    {
        $export = new class implements \Maatwebsite\Excel\Concerns\FromArray {
            public function array(): array
            {
                return [
                    ['tanggal_transaksi', 'cabang_id', 'barang_id', 'kuantitas'],
                    ['2024-01-15', '1', '1', '5'],
                    ['2024-01-16', '1', '2', '2'],
                ];
            }
        };

        return Excel::download($export, 'Template_Import_Penjualan_Historis.xlsx');
    }

    public function uploadExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:csv,txt,xlsx,xls|max:5120'
        ]);

        try {
            DB::beginTransaction();
            
            $kasirId = auth()->id();
            Excel::import(new TransaksiHistorisImport($kasirId), $request->file('file_excel'));

            DB::commit();
            return back()->with('success', 'Data riwayat transaksi berhasil di-import dari file. Silakan cek SES Debugger.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    public function storeManual(Request $request)
    {
        $request->validate([
            'tanggal_transaksi' => 'required|date',
            'cabang_id' => 'required|exists:cabangs,id',
            'barang_id' => 'required|exists:master_barang,id', // Fix table name
            'kuantitas' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();
            
            $barang = MasterBarang::find($request->barang_id);
            $hargaJual = $barang->harga_jual ?? 0;
            
            // Logic HPP (Harga Modal) FIFO (Yang paling pertama dibeli)
            $oldestBarangMasuk = \App\Models\BarangMasuk::where('barang_fisik_id', $request->barang_id)
                                ->orderBy('created_at', 'asc')
                                ->first();
            
            $hppSatuan = 0;
            if ($oldestBarangMasuk && $oldestBarangMasuk->qty_beli > 0) {
                $hppSatuan = $oldestBarangMasuk->total_harga_beli / $oldestBarangMasuk->qty_beli;
            }

            $totalTagihan = $request->kuantitas * $hargaJual;
            
            // 1. Buat Header Transaksi
            $trx = Transaksi::create([
                'cabang_id' => $request->cabang_id,
                'kasir_id' => auth()->id(),
                'metode_pembayaran' => 'cash', // Default untuk historical
                'total_tagihan' => $totalTagihan,
                'uang_diterima' => $totalTagihan,
                'kembalian' => 0,
                'fee_transaksi' => 0,
                'status' => 'success',
                'tanggal_transaksi' => $request->tanggal_transaksi . ' 12:00:00', // Jam default
                'created_at' => $request->tanggal_transaksi . ' 12:00:00',
                'updated_at' => $request->tanggal_transaksi . ' 12:00:00',
            ]);

            // 2. Buat Detail Transaksi
            DetailTransaksi::create([
                'transaksi_id' => $trx->id,
                'barang_id' => $request->barang_id,
                'kuantitas' => $request->kuantitas,
                'harga_satuan' => $hargaJual,
                'subtotal' => $totalTagihan,
                'hpp_satuan' => $hppSatuan,
                'created_at' => $request->tanggal_transaksi . ' 12:00:00',
                'updated_at' => $request->tanggal_transaksi . ' 12:00:00',
            ]);

            DB::commit();
            return back()->with('success', '1 data transaksi manual berhasil ditambahkan di masa lalu.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menambahkan data: ' . $e->getMessage());
        }
    }
}
