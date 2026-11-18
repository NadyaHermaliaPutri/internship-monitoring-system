<?php

namespace App\Http\Controllers;

use App\Http\Requests\Calendar\StoreCalendarExceptionRequest;
use App\Models\CalendarException;
use App\Services\AuditLogService;
use App\Services\WorkCalendarService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function __construct(
        protected WorkCalendarService $calendarService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Menampilkan kalender kerja visual bulanan untuk Admin/HR.
     */
    public function index(Request $request): View
    {
        $now = $this->calendarService->getNow();

        // Parameter tahun dan bulan yang dipilih (default bulan berjalan)
        $year = (int) $request->input('year', $now->year);
        $month = (int) $request->input('month', $now->month);

        // Validasi batasan bulan dan tahun
        if ($month < 1 || $month > 12) {
            $month = (int) $now->month;
        }
        if ($year < 2020 || $year > 2035) {
            $year = (int) $now->year;
        }

        $currentMonthDate = Carbon::createFromDate($year, $month, 1, config('wantrace.timezone', 'Asia/Jakarta'));
        $prevMonthDate = $currentMonthDate->copy()->subMonth();
        $nextMonthDate = $currentMonthDate->copy()->addMonth();

        // Batas awal grid (dimulai dari hari Senin pertama di minggu tersebut)
        $gridStart = $currentMonthDate->copy()->startOfWeek(Carbon::MONDAY);
        // Batas akhir grid (diakhiri hari Minggu terakhir di minggu tersebut)
        $gridEnd = $currentMonthDate->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        // Ambil seluruh pengecualian kalender dalam rentang grid kalender (1 query cepat)
        $exceptions = CalendarException::with('creator')
            ->betweenDates($gridStart->format('Y-m-d'), $gridEnd->format('Y-m-d'))
            ->get()
            ->keyBy(fn ($item) => $item->exception_date->format('Y-m-d'));

        $todayDateStr = $now->format('Y-m-d');
        $days = [];
        $cursor = $gridStart->copy();

        while ($cursor->lessThanOrEqualTo($gridEnd)) {
            $dateStr = $cursor->format('Y-m-d');
            $exception = $exceptions->get($dateStr);
            $dayStatus = $this->calendarService->getDayStatus($cursor);

            $days[] = [
                'date' => $dateStr,
                'day' => (int) $cursor->format('j'),
                'is_current_month' => $cursor->month === $month,
                'is_today' => $dateStr === $todayDateStr,
                'is_weekend' => $cursor->isWeekend(),
                'status' => $dayStatus,
                'exception' => $exception,
            ];

            $cursor->addDay();
        }

        // Bagi grid hari menjadi baris-baris mingguan (7 hari per minggu)
        $weeks = array_chunk($days, 7);

        // Daftar pengecualian kalender khusus di bulan yang sedang aktif
        $monthlyExceptions = CalendarException::with('creator')
            ->whereYear('exception_date', $year)
            ->whereMonth('exception_date', $month)
            ->orderBy('exception_date')
            ->get();

        // Ringkasan hari kerja dan hari libur bulan aktif
        $totalWorkingDays = $this->calendarService->getWorkingDaysCount(
            $currentMonthDate->copy()->startOfMonth(),
            $currentMonthDate->copy()->endOfMonth()
        );
        $totalCalendarDays = $currentMonthDate->daysInMonth;
        $totalHolidays = $totalCalendarDays - $totalWorkingDays;

        return view('admin.calendar.index', compact(
            'year',
            'month',
            'currentMonthDate',
            'prevMonthDate',
            'nextMonthDate',
            'weeks',
            'monthlyExceptions',
            'totalWorkingDays',
            'totalHolidays',
            'totalCalendarDays'
        ));
    }

    /**
     * Menambah atau memperbarui pengecualian tanggal kalender kerja (Admin/HR).
     * PENTING: Perubahan kalender tidak memodifikasi atau menghapus data attendance historis.
     */
    public function store(StoreCalendarExceptionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $admin = $request->user();
        $dateStr = $validated['exception_date'];

        $existing = CalendarException::whereDate('exception_date', $dateStr)->first();

        if ($existing) {
            $oldValues = [
                'type' => $existing->type,
                'name' => $existing->name,
                'description' => $existing->description,
            ];

            $existing->update([
                'type' => $validated['type'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'created_by' => $admin->id,
            ]);

            $newValues = [
                'type' => $existing->type,
                'name' => $existing->name,
                'description' => $existing->description,
            ];

            $this->auditLogService->log(
                action: 'calendar_exception_updated',
                entityType: 'calendar_exceptions',
                entityId: $existing->id,
                oldValues: $oldValues,
                newValues: $newValues,
                actor: $admin
            );

            $message = 'Pengecualian tanggal kalender berhasil diperbarui.';
        } else {
            $newException = CalendarException::create([
                'exception_date' => $dateStr,
                'type' => $validated['type'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'created_by' => $admin->id,
            ]);

            $this->auditLogService->log(
                action: 'calendar_exception_created',
                entityType: 'calendar_exceptions',
                entityId: $newException->id,
                oldValues: null,
                newValues: [
                    'exception_date' => $newException->exception_date->format('Y-m-d'),
                    'type' => $newException->type,
                    'name' => $newException->name,
                    'description' => $newException->description,
                ],
                actor: $admin
            );

            $message = 'Pengecualian kalender berhasil ditambahkan.';
        }

        $carbonDate = Carbon::parse($dateStr);

        return redirect()
            ->route('admin.calendar.index', ['year' => $carbonDate->year, 'month' => $carbonDate->month])
            ->with('success', $message);
    }

    /**
     * Menghapus override pengecualian kalender (Admin/HR).
     * Tanggal akan otomatis kembali ke default operasional (Senin-Jumat kerja, Sabtu-Minggu libur).
     */
    public function destroy(CalendarException $calendarException): RedirectResponse
    {
        $date = $calendarException->exception_date;
        $oldValues = [
            'exception_date' => $date->format('Y-m-d'),
            'type' => $calendarException->type,
            'name' => $calendarException->name,
            'description' => $calendarException->description,
        ];

        $calendarException->delete();

        $this->auditLogService->log(
            action: 'calendar_exception_deleted',
            entityType: 'calendar_exceptions',
            entityId: $calendarException->id,
            oldValues: $oldValues,
            newValues: null,
            actor: auth()->user()
        );

        return redirect()
            ->route('admin.calendar.index', ['year' => $date->year, 'month' => $date->month])
            ->with('success', "Pengecualian tanggal {$date->translatedFormat('d F Y')} berhasil dihapus. Tanggal kembali ke aturan default.");
    }
}
