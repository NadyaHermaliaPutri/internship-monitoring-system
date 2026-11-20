<?php

namespace Tests\Unit;

use App\Models\CalendarException;
use App\Models\User;
use App\Services\WorkCalendarService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkCalendarServiceTest extends TestCase
{
    use RefreshDatabase;

    protected WorkCalendarService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(WorkCalendarService::class);
    }

    public function test_monday_to_friday_are_working_days_by_default(): void
    {
        // 2026-11-16 (Senin) s/d 2026-11-20 (Jumat)
        $monday = Carbon::parse('2026-11-16');
        $tuesday = Carbon::parse('2026-11-17');
        $wednesday = Carbon::parse('2026-11-18');
        $thursday = Carbon::parse('2026-11-19');
        $friday = Carbon::parse('2026-11-20');

        $this->assertTrue($this->service->isWorkingDay($monday));
        $this->assertTrue($this->service->isWorkingDay($tuesday));
        $this->assertTrue($this->service->isWorkingDay($wednesday));
        $this->assertTrue($this->service->isWorkingDay($thursday));
        $this->assertTrue($this->service->isWorkingDay($friday));

        $this->assertFalse($this->service->isHoliday($monday));
    }

    public function test_saturday_and_sunday_are_holidays_by_default(): void
    {
        // 2026-11-21 (Sabtu), 2026-11-22 (Minggu)
        $saturday = Carbon::parse('2026-11-21');
        $sunday = Carbon::parse('2026-11-22');

        $this->assertFalse($this->service->isWorkingDay($saturday));
        $this->assertFalse($this->service->isWorkingDay($sunday));

        $this->assertTrue($this->service->isHoliday($saturday));
        $this->assertTrue($this->service->isHoliday($sunday));
    }

    public function test_calendar_exception_holiday_overrides_weekday(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Rabu, 2026-11-18 dijadikan Hari Libur Nasional
        CalendarException::create([
            'exception_date' => '2026-11-18',
            'type' => 'holiday',
            'name' => 'Hari Libur Nasional Uji Coba',
            'description' => 'Libur khusus',
            'created_by' => $admin->id,
        ]);

        $wednesday = Carbon::parse('2026-11-18');

        $this->assertFalse($this->service->isWorkingDay($wednesday));
        $this->assertTrue($this->service->isHoliday($wednesday));
    }

    public function test_calendar_exception_working_day_overrides_weekend(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Sabtu, 2026-11-21 dijadikan Hari Kerja Khusus
        CalendarException::create([
            'exception_date' => '2026-11-21',
            'type' => 'working_day',
            'name' => 'Hari Kerja Khusus Sabtu Lembur',
            'description' => 'Penyesuaian jadwal',
            'created_by' => $admin->id,
        ]);

        $saturday = Carbon::parse('2026-11-21');

        $this->assertTrue($this->service->isWorkingDay($saturday));
        $this->assertFalse($this->service->isHoliday($saturday));
    }

    public function test_count_working_days_between_two_dates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Rentang 2026-11-16 (Senin) s/d 2026-11-22 (Minggu)
        // Default: 5 hari kerja (Senin - Jumat)
        $count = $this->service->countWorkingDays('2026-11-16', '2026-11-22');
        $this->assertEquals(5, $count);

        // Jika hari Rabu (2026-11-18) libur
        CalendarException::create([
            'exception_date' => '2026-11-18',
            'type' => 'holiday',
            'name' => 'Libur',
            'created_by' => $admin->id,
        ]);

        $newCount = $this->service->countWorkingDays('2026-11-16', '2026-11-22');
        $this->assertEquals(4, $newCount);
    }
}
