<?php

namespace App\Services;

use App\Models\CalendarException;
use Carbon\Carbon;

class WorkCalendarService
{
    /**
     * Timezone standar sistem PT WAN Teknologi Internasional.
     */
    protected string $timezone;

    public function __construct()
    {
        $this->timezone = config('wantrace.timezone', 'Asia/Jakarta');
    }

    /**
     * Mendapatkan waktu/tanggal hari ini dalam timezone server Asia/Jakarta.
     */
    public function getNow(): Carbon
    {
        return Carbon::now($this->timezone);
    }

    /**
     * Memformat tanggal ke Carbon instance dengan timezone Asia/Jakarta.
     */
    public function parseDate(Carbon|string $date): Carbon
    {
        if ($date instanceof Carbon) {
            return $date->copy()->setTimezone($this->timezone)->startOfDay();
        }

        return Carbon::parse($date, $this->timezone)->startOfDay();
    }

    /**
     * Menentukan apakah suatu tanggal adalah hari kerja.
     * Aturan:
     * 1. Cek calendar_exceptions:
     *    - type = 'working_day' -> hari kerja (meskipun akhir pekan)
     *    - type = 'holiday' -> libur (meskipun hari kerja normal)
     * 2. Jika tidak ada override:
     *    - Senin - Jumat: hari kerja
     *    - Sabtu & Minggu: libur
     */
    public function isWorkingDay(Carbon|string $date): bool
    {
        $carbonDate = $this->parseDate($date);
        $dateString = $carbonDate->format('Y-m-d');

        $exception = CalendarException::whereDate('exception_date', $dateString)->first();

        if ($exception) {
            return $exception->type === 'working_day';
        }

        // Default: Senin (1) sampai Jumat (5) adalah hari kerja
        return $carbonDate->isWeekday();
    }

    /**
     * Menentukan apakah suatu tanggal adalah hari libur (akhir pekan atau tanggal merah).
     */
    public function isHoliday(Carbon|string $date): bool
    {
        return ! $this->isWorkingDay($date);
    }

    /**
     * Mendapatkan record pengecualian kalender jika ada.
     */
    public function getCalendarException(Carbon|string $date): ?CalendarException
    {
        $dateString = $this->parseDate($date)->format('Y-m-d');
        return CalendarException::whereDate('exception_date', $dateString)->first();
    }

    /**
     * Mendapatkan status lengkap suatu hari beserta alasannya.
     */
    public function getDayStatus(Carbon|string $date): array
    {
        $carbonDate = $this->parseDate($date);
        $dateString = $carbonDate->format('Y-m-d');
        $exception = CalendarException::whereDate('exception_date', $dateString)->first();

        if ($exception) {
            if ($exception->type === 'working_day') {
                return [
                    'date' => $dateString,
                    'is_working_day' => true,
                    'is_holiday' => false,
                    'status_type' => 'exception_working_day',
                    'label' => 'Hari Kerja Khusus',
                    'reason' => $exception->name,
                    'description' => $exception->description,
                ];
            }

            return [
                'date' => $dateString,
                'is_working_day' => false,
                'is_holiday' => true,
                'status_type' => 'exception_holiday',
                'label' => 'Hari Libur / Tanggal Merah',
                'reason' => $exception->name,
                'description' => $exception->description,
            ];
        }

        if ($carbonDate->isWeekend()) {
            return [
                'date' => $dateString,
                'is_working_day' => false,
                'is_holiday' => true,
                'status_type' => 'regular_weekend',
                'label' => 'Akhir Pekan',
                'reason' => $carbonDate->isoFormat('dddd') . ' (Libur Otomatis)',
                'description' => null,
            ];
        }

        return [
            'date' => $dateString,
            'is_working_day' => true,
            'is_holiday' => false,
            'status_type' => 'regular_working_day',
            'label' => 'Hari Kerja Normal',
            'reason' => 'Senin–Jumat Operasional Kantor',
            'description' => null,
        ];
    }

    /**
     * Menghitung total hari kerja antara dua tanggal inklusif.
     */
    public function getWorkingDaysCount(Carbon|string $startDate, Carbon|string $endDate): int
    {
        return count($this->getWorkingDaysList($startDate, $endDate));
    }

    /**
     * Alias untuk getWorkingDaysCount.
     */
    public function countWorkingDays(Carbon|string $startDate, Carbon|string $endDate): int
    {
        return $this->getWorkingDaysCount($startDate, $endDate);
    }

    /**
     * Mendapatkan daftar tanggal hari kerja (array string Y-m-d) dalam rentang waktu.
     */
    public function getWorkingDaysList(Carbon|string $startDate, Carbon|string $endDate): array
    {
        $start = $this->parseDate($startDate);
        $end = $this->parseDate($endDate);

        if ($start->greaterThan($end)) {
            return [];
        }

        // Ambil seluruh exceptions dalam rentang untuk optimasi query (1 query)
        $exceptions = CalendarException::betweenDates($start->format('Y-m-d'), $end->format('Y-m-d'))
            ->get()
            ->keyBy(fn ($item) => $item->exception_date->format('Y-m-d'));

        $workingDays = [];
        $current = $start->copy();

        while ($current->lessThanOrEqualTo($end)) {
            $currentStr = $current->format('Y-m-d');
            $exception = $exceptions->get($currentStr);

            if ($exception) {
                if ($exception->type === 'working_day') {
                    $workingDays[] = $currentStr;
                }
            } else {
                if ($current->isWeekday()) {
                    $workingDays[] = $currentStr;
                }
            }

            $current->addDay();
        }

        return $workingDays;
    }
}
