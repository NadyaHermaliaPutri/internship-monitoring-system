<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReportExcelExport implements FromView, ShouldAutoSize
{
    public function __construct(
        protected string $reportType,
        protected array $reportData
    ) {}

    public function view(): View
    {
        return view('admin.reports.excel.' . $this->reportType, [
            'data' => $this->reportData,
            'metadata' => $this->reportData['metadata'] ?? [],
            'summary' => $this->reportData['summary'] ?? [],
        ]);
    }
}
