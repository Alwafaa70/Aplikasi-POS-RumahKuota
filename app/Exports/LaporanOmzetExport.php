<?php

namespace App\Exports;

use App\Models\Transaksi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LaporanOmzetExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Transaksi::with(['cabang', 'kasir'])->orderBy('tanggal_transaksi', 'asc');

        if (!empty($this->filters['start_date'])) {
            $query->whereDate('tanggal_transaksi', '>=', $this->filters['start_date']);
        }
        
        if (!empty($this->filters['end_date'])) {
            $query->whereDate('tanggal_transaksi', '<=', $this->filters['end_date']);
        }
        
        if (!empty($this->filters['cabang_id'])) {
            $query->where('cabang_id', $this->filters['cabang_id']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID Transaksi',
            'Tanggal Transaksi',
            'Cabang',
            'Kasir',
            'Metode Pembayaran',
            'Total Tagihan (Rp)'
        ];
    }

    public function map($transaksi): array
    {
        return [
            $transaksi->id,
            $transaksi->tanggal_transaksi,
            $transaksi->cabang ? $transaksi->cabang->nama_cabang : '-',
            $transaksi->kasir ? $transaksi->kasir->name : '-',
            strtoupper($transaksi->metode_pembayaran),
            $transaksi->total_tagihan
        ];
    }
}
