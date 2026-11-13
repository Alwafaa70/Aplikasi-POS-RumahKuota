<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\PembelianSupplier;

class PembelianExport implements FromView, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function view(): View
    {
        $query = PembelianSupplier::with(['supplier', 'admin', 'detailPembelian.barangFisik'])
            ->orderBy('tanggal_pembelian', 'desc');

        if ($this->startDate) {
            $query->whereDate('tanggal_pembelian', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('tanggal_pembelian', '<=', $this->endDate);
        }

        $pembelians = $query->get();

        return view('admin.pembelian.historis_pdf', [
            'pembelians' => $pembelians,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'isExcel' => true
        ]);
    }
}
