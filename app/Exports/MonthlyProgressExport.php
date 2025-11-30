<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonthlyProgressExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $project;
    protected $reportDataOriginal;
    protected $reportDataAdditional;
    protected $month;
    protected $grandTotalContract;
    protected $totalOriginalContract;
    protected $totalAdditionalContract;

    public function __construct($project, $reportDataOriginal, $reportDataAdditional, $month, $grandTotalContract, $totalOriginalContract, $totalAdditionalContract)
    {
        $this->project = $project;
        $this->reportDataOriginal = $reportDataOriginal;
        $this->reportDataAdditional = $reportDataAdditional;
        $this->month = $month;
        $this->grandTotalContract = $grandTotalContract;
        $this->totalOriginalContract = $totalOriginalContract;
        $this->totalAdditionalContract = $totalAdditionalContract;
    }

    public function view(): View
    {
        return view('reports.monthly_progress_excel', [
            'project' => $this->project,
            'reportDataOriginal' => $this->reportDataOriginal,
            'reportDataAdditional' => $this->reportDataAdditional,
            'month' => $this->month,
            'grandTotalContract' => $this->grandTotalContract,
            'totalOriginalContract' => $this->totalOriginalContract,
            'totalAdditionalContract' => $this->totalAdditionalContract,
            'isExport' => true // Flag to potentially adjust view for export
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1    => ['font' => ['bold' => true]],
        ];
    }
}
