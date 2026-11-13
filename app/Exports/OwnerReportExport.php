<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\ProfitabilitySheet;
use App\Exports\Sheets\DistribusiSheet;
use App\Exports\Sheets\BlindClosingSheet;

class OwnerReportExport implements WithMultipleSheets
{
    use Exportable;

    protected $profitability;
    protected $distribusi;
    protected $monitoringClosing;

    public function __construct($profitability, $distribusi, $monitoringClosing)
    {
        $this->profitability = $profitability;
        $this->distribusi = $distribusi;
        $this->monitoringClosing = $monitoringClosing;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new ProfitabilitySheet($this->profitability);
        $sheets[] = new DistribusiSheet($this->distribusi);
        $sheets[] = new BlindClosingSheet($this->monitoringClosing);

        return $sheets;
    }
}
