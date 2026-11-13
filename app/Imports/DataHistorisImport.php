<?php

namespace App\Imports;

use App\Models\DataHistorisAgregasi;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DataHistorisImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Asumsi format kolom Excel (Header):
        // cabang_id, barang_id, periode_bulan, total_unit_terjual
        
        // Skip jika data kosong
        if (!isset($row['cabang_id']) || !isset($row['barang_id']) || !isset($row['periode_bulan'])) {
            return null;
        }

        return new DataHistorisAgregasi([
            'cabang_id'          => $row['cabang_id'],
            'barang_id'          => $row['barang_id'],
            'periode_bulan'      => $row['periode_bulan'], // Contoh format: 2024-01
            'total_unit_terjual' => $row['total_unit_terjual'] ?? 0,
        ]);
    }
}
