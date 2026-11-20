<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\CalendarException;
use App\Models\MediaFile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_calendar_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($admin)->get('/admin/kalender');

        $response->assertStatus(200);
        $response->assertSee('Kalender Kerja');
    }

    public function test_admin_can_view_calendar_index_with_populated_exceptions(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        Carbon::setTestNow(Carbon::parse('2026-11-12 10:00:00', 'Asia/Jakarta'));

        // Pengecualian 1: Hari Libur
        CalendarException::create([
            'exception_date' => '2026-11-10',
            'type' => 'holiday',
            'name' => 'Libur Nasional Hari Pahlawan',
            'description' => 'Memperingati hari pahlawan',
            'created_by' => $admin->id,
        ]);

        // Pengecualian 2: Hari Kerja Khusus di Akhir Pekan
        CalendarException::create([
            'exception_date' => '2026-11-15',
            'type' => 'working_day',
            'name' => 'Sabtu Kerja Khusus Pengganti',
            'description' => 'Operasional khusus',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get('/admin/kalender?month=11&year=2026');

        $response->assertStatus(200);
        $response->assertSee('Kalender Kerja');
        $response->assertSee('Libur Nasional Hari Pahlawan');
        $response->assertSee('HARI LIBUR');
        $response->assertSee('Sabtu Kerja Khusus Pengganti');
        $response->assertSee('HARI KERJA KHUSUS');
    }

    public function test_intern_and_pic_cannot_access_calendar_management(): void
    {
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);

        $this->actingAs($intern)->get('/admin/kalender')->assertStatus(403);
        $this->actingAs($pic)->get('/admin/kalender')->assertStatus(403);

        $this->actingAs($intern)->post('/admin/kalender', [
            'exception_date' => '2026-11-20',
            'type' => 'holiday',
            'name' => 'Libur',
        ])->assertStatus(403);

        $this->actingAs($pic)->post('/admin/kalender', [
            'exception_date' => '2026-11-20',
            'type' => 'holiday',
            'name' => 'Libur',
        ])->assertStatus(403);
    }

    public function test_admin_can_create_update_and_delete_calendar_exception(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        // 1. Create via POST /admin/kalender
        $response = $this->actingAs($admin)->post('/admin/kalender', [
            'exception_date' => '2026-12-25',
            'type' => 'holiday',
            'name' => 'Hari Raya Natal',
            'description' => 'Libur nasional bersama',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Pengecualian kalender kerja berhasil ditambahkan.');
        $this->assertTrue(CalendarException::whereDate('exception_date', '2026-12-25')->exists());

        $exception = CalendarException::whereDate('exception_date', '2026-12-25')->first();
        $this->assertEquals('Hari Raya Natal', $exception->name);

        // 2. Update via POST /admin/kalender (upsert)
        $updateResponse = $this->actingAs($admin)->post('/admin/kalender', [
            'exception_date' => '2026-12-25',
            'type' => 'holiday',
            'name' => 'Hari Raya Natal & Cuti Bersama',
            'description' => 'Diperbarui',
        ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('success', 'Pengecualian kalender kerja berhasil diperbarui.');
        $exception->refresh();
        $this->assertEquals('Hari Raya Natal & Cuti Bersama', $exception->name);

        // 3. Delete via DELETE /admin/kalender/{calendarException}
        $deleteResponse = $this->actingAs($admin)->delete("/admin/kalender/{$exception->id}");

        $deleteResponse->assertRedirect();
        $deleteResponse->assertSessionHas('success', 'Pengecualian kalender kerja berhasil dihapus.');
        $this->assertFalse(CalendarException::whereDate('exception_date', '2026-12-25')->exists());
    }

    public function test_modifying_calendar_exceptions_does_not_alter_historical_attendance_records(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // Buat dummy foto dengan file_size_bytes
        $media = MediaFile::create([
            'disk' => 'local_private',
            'file_path' => 'evidence/test.jpg',
            'file_name' => 'test.jpg',
            'original_name' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => 1024,
            'category' => 'attendance_photo',
            'owner_user_id' => $intern->id,
        ]);

        // Presensi historis pada tanggal lampau (2026-11-10)
        $pastAttendance = Attendance::create([
            'intern_user_id' => $intern->id,
            'attendance_date' => '2026-11-10',
            'checked_in_at' => '2026-11-10 07:55:00',
            'status' => 'on_time',
            'photo_media_id' => $media->id,
            'notes' => 'Presensi resmi tersimpan',
        ]);

        $attendanceCountBefore = Attendance::count();

        // Admin menetapkan tanggal 2026-11-10 sebagai holiday di kemudian hari
        $this->actingAs($admin)->post('/admin/kalender', [
            'exception_date' => '2026-11-10',
            'type' => 'holiday',
            'name' => 'Libur Dadakan Lampau',
        ]);

        // Data presensi tetap utuh
        $this->assertEquals($attendanceCountBefore, Attendance::count());
        $pastAttendance->refresh();
        $this->assertEquals('on_time', $pastAttendance->status);
        $this->assertEquals('07:55:00', $pastAttendance->checked_in_at->format('H:i:s'));

        // Admin kemudian menghapus exception tersebut
        $exception = CalendarException::whereDate('exception_date', '2026-11-10')->first();
        $this->actingAs($admin)->delete("/admin/kalender/{$exception->id}");

        // Data presensi tetap tidak terpengaruh
        $this->assertEquals($attendanceCountBefore, Attendance::count());
        $this->assertDatabaseHas('attendance', [
            'id' => $pastAttendance->id,
            'status' => 'on_time',
        ]);
    }
}
