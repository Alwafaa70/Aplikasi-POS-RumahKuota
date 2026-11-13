<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProfitabilitySheet implements FromArray, WithTitle, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return [
            [
                'Total Pendapatan (Omzet)',
                $this->data['total_pendapatan']
            ],
            [
                'Total Harga Pokok Penjualan (HPP)',
                $this->data['total_hpp']
            ],
            [
                'Total Fee Admin (Digital)',
                $this->data['total_fee_digital_b']
            ],
            [
                'Laba Bersih Keseluruhan',
                $this->data['laba_kotor']
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Komponen',
            'Nilai (Rp)'
        ];
    }

    public function title(): string
    {
        return 'Laba & Rugi';
    }
}
