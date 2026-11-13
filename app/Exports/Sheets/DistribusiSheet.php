<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class DistribusiSheet implements FromCollection, WithTitle, WithHeadings, WithMapping
{
    protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function map($mutasi): array
    {
        return [
            $mutasi->tanggal_mutasi,
            $mutasi->cabangTujuan ? $mutasi->cabangTujuan->nama_cabang : 'Tidak Diketahui',
            $mutasi->status,
            $mutasi->nilai_modal_dipindahkan ?? 0,
            $mutasi->keterangan
        ];
    }

    public function headings(): array
    {
        return [
            'Tanggal Distribusi',
            'Cabang Tujuan',
            'Status Pengiriman',
            'Nilai HPP Dipindahkan (Rp)',
            'Keterangan'
        ];
    }

    public function title(): string
    {
        return 'Distribusi Aset';
    }
}
