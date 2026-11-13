<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\PusatAplikasiLayanan;
use App\Models\KasPusat;

class HistorisPusatExport implements FromView, ShouldAutoSize
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
        $controller = app(\App\Http\Controllers\PusatAplikasiLayananController::class);
        $data = $controller->getHistorisData($this->startDate, $this->endDate);

        $totalDigital = PusatAplikasiLayanan::sum('saldo_sekarang');
        $kasPusat = KasPusat::first();
        $totalFisik = $kasPusat ? $kasPusat->saldo : 0;

        return view('pusat_aplikasi_layanan.historis_pdf', array_merge($data, [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'totalDigital' => $totalDigital,
            'totalFisik' => $totalFisik,
            'isExcel' => true
        ]));
    }
}
