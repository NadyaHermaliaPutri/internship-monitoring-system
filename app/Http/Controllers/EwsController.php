<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ews\UpdateEwsSettingRequest;
use App\Models\EwsSetting;
use App\Services\EwsService;
use App\Services\WorkCalendarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class EwsController extends Controller
{
    public function __construct(
        protected EwsService $ewsService,
        protected WorkCalendarService $calendarService
    ) {}

    /**
     * Halaman Monitoring EWS Global untuk Administrator / HR.
     */
    public function adminIndex(Request $request): View
    {
        $timezone = config('wantrace.timezone', 'Asia/Jakarta');
        $today = Carbon::now($timezone)->format('Y-m-d');
        $date = $request->query('date', $today);
        $type = $request->query('type', 'all');

        $summary = $this->ewsService->getAdminEwsSummary($date);
        $warnings = $this->filterWarnings($summary['warnings_list'], $type);
        $settings = EwsSetting::getSettings();
        $isWorkingDay = $this->calendarService->isWorkingDay($date);

        return view('ews.index', [
            'summary' => $summary,
            'warnings' => $warnings,
            'date' => $date,
            'today' => $today,
            'type' => $type,
            'settings' => $settings,
            'isWorkingDay' => $isWorkingDay,
            'isPicView' => false,
        ]);
    }

    /**
     * Memperbarui pengaturan jam cutoff EWS (Hanya Admin / HR).
     */
    public function adminUpdateSettings(UpdateEwsSettingRequest $request): RedirectResponse
    {
        $this->ewsService->updateCutoffSettings(
            attendanceCutoff: $request->validated('attendance_cutoff_time'),
            logbookCutoff: $request->validated('logbook_cutoff_time'),
            admin: $request->user()
        );

        return redirect()->route('admin.ews.index')
            ->with('success', 'Pengaturan batas jam cutoff EWS berhasil diperbarui.');
    }

    /**
     * Halaman Monitoring EWS Khusus Peserta Bimbingan PIC.
     */
    public function picIndex(Request $request): View
    {
        $timezone = config('wantrace.timezone', 'Asia/Jakarta');
        $today = Carbon::now($timezone)->format('Y-m-d');
        $date = $request->query('date', $today);
        $type = $request->query('type', 'all');

        $summary = $this->ewsService->getPicEwsSummary($request->user()->id, $date);
        $warnings = $this->filterWarnings($summary['warnings_list'], $type);
        $settings = EwsSetting::getSettings();
        $isWorkingDay = $this->calendarService->isWorkingDay($date);

        return view('ews.index', [
            'summary' => $summary,
            'warnings' => $warnings,
            'date' => $date,
            'today' => $today,
            'type' => $type,
            'settings' => $settings,
            'isWorkingDay' => $isWorkingDay,
            'isPicView' => true,
        ]);
    }

    /**
     * Menyaring daftar peringatan EWS berdasarkan kategori indikator yang dipilih.
     */
    protected function filterWarnings($warnings, string $type)
    {
        return match ($type) {
            'attendance' => $warnings->filter(fn ($item) => ($item['type'] ?? '') === 'unsubmitted_attendance'),
            'logbook' => $warnings->filter(fn ($item) => ($item['type'] ?? '') === 'missing_logbook'),
            'task' => $warnings->filter(fn ($item) => ($item['type'] ?? '') === 'overdue_task'),
            default => $warnings,
        };
    }
}
