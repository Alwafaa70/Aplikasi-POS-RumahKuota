<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class BlindClosingSheet implements FromCollection, WithTitle, WithHeadings, WithMapping
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

    public function map($closing): array
    {
        return [
            $closing->created_at->format('Y-m-d H:i'),
            $closing->cabang ? $closing->cabang->nama_cabang : 'Tidak Diketahui',
            $closing->kasir ? $closing->kasir->name : 'Tidak Diketahui',
            $closing->total_sistem,
            $closing->total_aktual_input,
            $closing->selisih,
            $closing->status
        ];
    }

    public function headings(): array
    {
        return [
            'Waktu Closing',
            'Cabang',
            'Nama Kasir',
            'Ekspektasi Sistem (Rp)',
            'Setoran Kasir (Rp)',
            'Selisih/Minus (Rp)',
            'Status'
        ];
    }

    public function title(): string
    {
        return 'Detail Blind Closing';
    }
}
