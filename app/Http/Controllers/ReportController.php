<?php

namespace App\Http\Controllers;

use App\Exports\ReportExcelExport;
use App\Http\Requests\Report\ReportFilterRequest;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Halaman index dan filter preview laporan (Admin/HR Only).
     */
    public function index(Request $request): View
    {
        $timezone = config('wantrace.timezone', 'Asia/Jakarta');
        $now = Carbon::now($timezone);

        $reportType = $request->query('report_type', 'attendance');
        $internId = $request->query('intern_id') ? (int) $request->query('intern_id') : null;
        $startDate = $request->query('start_date', $now->copy()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', $now->copy()->endOfMonth()->format('Y-m-d'));

        // Validasi format tanggal & tipe dasar jika ada query string yang dikirim
        if ($request->has('report_type') && ! in_array($reportType, ['attendance', 'leave', 'logbook', 'monitoring'], true)) {
            $reportType = 'attendance';
        }

        if ($startDate > $endDate) {
            $endDate = $startDate;
        }

        $interns = User::where('role', 'intern')->orderBy('name', 'asc')->get();

        $reportData = $this->reportService->getReportData(
            reportType: $reportType,
            startDate: $startDate,
            endDate: $endDate,
            internId: $internId
        );

        return view('admin.reports.index', [
            'interns' => $interns,
            'reportType' => $reportType,
            'internId' => $internId,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportData' => $reportData,
        ]);
    }

    /**
     * Mengunduh rekap laporan dalam format PDF (A4).
     */
    public function exportPdf(ReportFilterRequest $request): Response
    {
        $validated = $request->validated();
        $reportType = $validated['report_type'];
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        $internId = ! empty($validated['intern_id']) ? (int) $validated['intern_id'] : null;

        $reportData = $this->reportService->getReportData(
            reportType: $reportType,
            startDate: $startDate,
            endDate: $endDate,
            internId: $internId
        );

        $pdf = Pdf::loadView('admin.reports.pdf.' . $reportType, [
            'data' => $reportData,
            'metadata' => $reportData['metadata'] ?? [],
            'summary' => $reportData['summary'] ?? [],
        ])->setPaper('a4', 'portrait');

        $filename = "laporan_{$reportType}_{$startDate}_sd_{$endDate}.pdf";

        // Jejak audit ekspor laporan
        $this->auditLogService->log(
            action: 'report_exported',
            entityType: 'report',
            entityId: null,
            oldValues: null,
            newValues: [
                'format' => 'pdf',
                'report_type' => $reportType,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'intern_id' => $internId,
                'filename' => $filename,
            ],
            actor: $request->user()
        );

        return $pdf->download($filename);
    }

    /**
     * Mengunduh rekap laporan dalam format Excel (.xlsx).
     */
    public function exportExcel(ReportFilterRequest $request): BinaryFileResponse
    {
        $validated = $request->validated();
        $reportType = $validated['report_type'];
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        $internId = ! empty($validated['intern_id']) ? (int) $validated['intern_id'] : null;

        $reportData = $this->reportService->getReportData(
            reportType: $reportType,
            startDate: $startDate,
            endDate: $endDate,
            internId: $internId
        );

        $filename = "laporan_{$reportType}_{$startDate}_sd_{$endDate}.xlsx";

        // Jejak audit ekspor laporan
        $this->auditLogService->log(
            action: 'report_exported',
            entityType: 'report',
            entityId: null,
            oldValues: null,
            newValues: [
                'format' => 'excel',
                'report_type' => $reportType,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'intern_id' => $internId,
                'filename' => $filename,
            ],
            actor: $request->user()
        );

        return Excel::download(new ReportExcelExport($reportType, $reportData), $filename);
    }
}
