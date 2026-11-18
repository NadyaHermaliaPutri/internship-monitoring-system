<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\CalendarException;
use App\Models\LeaveRequest;
use App\Models\MediaFile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local_private');
    }

    public function test_intern_can_check_in_on_working_day_with_valid_photo(): void
    {
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // Simulasikan hari kerja (Rabu 2026-11-18 jam 08:00:00 WIB)
        Carbon::setTestNow(Carbon::parse('2026-11-18 08:00:00', 'Asia/Jakarta'));

        $photo = UploadedFile::fake()->image('selfie.jpg', 640, 480);

        $response = $this->actingAs($intern)->post('/intern/presensi', [
            'photo' => $photo,
        ]);

        $response->assertRedirect('/intern/presensi');
        $response->assertSessionHas('success');

        // Pastikan record attendance tersimpan dengan status on_time
        $attendance = Attendance::where('intern_user_id', $intern->id)->first();
        $this->assertNotNull($attendance);
        $this->assertEquals('on_time', $attendance->status);
        $this->assertEquals('08:00:00', $attendance->checked_in_at->format('H:i:s'));

        // Pastikan media file tersimpan di database dan disk local_private
        $this->assertNotNull($attendance->photo_media_id);

        $media = MediaFile::find($attendance->photo_media_id);
        $this->assertNotNull($media);
        $this->assertEquals('local_private', $media->disk);
        $this->assertEquals('attendance_photo', $media->category);
        Storage::disk('local_private')->assertExists($media->file_path);
    }

    public function test_cutoff_tolerance_rules_at_08_05_wib(): void
    {
        // 1. Tepat 08:05:00 WIB => on_time
        $intern1 = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        Carbon::setTestNow(Carbon::parse('2026-11-18 08:05:00', 'Asia/Jakarta'));

        $this->actingAs($intern1)->post('/intern/presensi', [
            'photo' => UploadedFile::fake()->image('selfie1.jpg'),
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('attendance', [
            'intern_user_id' => $intern1->id,
            'status' => 'on_time',
        ]);

        // 2. Detik terakhir toleransi 08:05:59 WIB => on_time
        $intern2 = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        Carbon::setTestNow(Carbon::parse('2026-11-18 08:05:59', 'Asia/Jakarta'));

        $this->actingAs($intern2)->post('/intern/presensi', [
            'photo' => UploadedFile::fake()->image('selfie2.jpg'),
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('attendance', [
            'intern_user_id' => $intern2->id,
            'status' => 'on_time',
        ]);

        // 3. Melewati batas 08:06:00 WIB => late (terlambat)
        $intern3 = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        Carbon::setTestNow(Carbon::parse('2026-11-18 08:06:00', 'Asia/Jakarta'));

        $this->actingAs($intern3)->post('/intern/presensi', [
            'photo' => UploadedFile::fake()->image('selfie3.jpg'),
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('attendance', [
            'intern_user_id' => $intern3->id,
            'status' => 'late',
        ]);
    }

    public function test_duplicate_check_in_on_same_date_is_prevented(): void
    {
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        Carbon::setTestNow(Carbon::parse('2026-11-18 07:45:00', 'Asia/Jakarta'));

        // Check-in pertama sukses
        $this->actingAs($intern)->post('/intern/presensi', [
            'photo' => UploadedFile::fake()->image('selfie_first.jpg'),
        ])->assertSessionHas('success');

        $this->assertEquals(1, Attendance::where('intern_user_id', $intern->id)->count());

        // Check-in kedua pada hari yang sama ditolak
        $secondResponse = $this->actingAs($intern)->post('/intern/presensi', [
            'photo' => UploadedFile::fake()->image('selfie_second.jpg'),
        ]);

        $secondResponse->assertSessionHas('error');
        $this->assertEquals(1, Attendance::where('intern_user_id', $intern->id)->count());
    }

    public function test_check_in_rejected_on_weekend_and_holiday_unless_override_working_day(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // A. Hari Sabtu normal (2026-11-21) ditolak
        Carbon::setTestNow(Carbon::parse('2026-11-21 08:00:00', 'Asia/Jakarta'));
        $this->actingAs($intern)->post('/intern/presensi', [
            'photo' => UploadedFile::fake()->image('selfie.jpg'),
        ])->assertSessionHas('error');
        $this->assertEquals(0, Attendance::count());

        // B. Hari kerja (Rabu 2026-11-18) yang di-override sebagai holiday ditolak
        CalendarException::create([
            'exception_date' => '2026-11-18',
            'type' => 'holiday',
            'name' => 'Libur Nasional',
            'created_by' => $admin->id,
        ]);
        Carbon::setTestNow(Carbon::parse('2026-11-18 08:00:00', 'Asia/Jakarta'));
        $this->actingAs($intern)->post('/intern/presensi', [
            'photo' => UploadedFile::fake()->image('selfie.jpg'),
        ])->assertSessionHas('error');
        $this->assertEquals(0, Attendance::count());

        // C. Hari Minggu (2026-11-22) di-override sebagai working_day khusus => berhasil
        CalendarException::create([
            'exception_date' => '2026-11-22',
            'type' => 'working_day',
            'name' => 'Kerja Khusus Event',
            'created_by' => $admin->id,
        ]);
        Carbon::setTestNow(Carbon::parse('2026-11-22 08:00:00', 'Asia/Jakarta'));
        $this->actingAs($intern)->post('/intern/presensi', [
            'photo' => UploadedFile::fake()->image('selfie.jpg'),
        ])->assertSessionHas('success');
        $this->assertEquals(1, Attendance::count());
    }

    public function test_check_in_rejected_if_intern_has_approved_leave_on_that_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // Buat dummy media untuk leave evidence
        $media = MediaFile::create([
            'disk' => 'local_private',
            'file_path' => 'evidence/surat.pdf',
            'file_name' => 'surat.pdf',
            'original_name' => 'surat.pdf',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 2048,
            'category' => 'leave_evidence',
            'owner_user_id' => $intern->id,
        ]);

        // Izin sah yang sudah disetujui HR untuk tanggal 2026-11-18
        LeaveRequest::create([
            'intern_user_id' => $intern->id,
            'leave_type' => 'sick',
            'start_date' => '2026-11-18',
            'end_date' => '2026-11-18',
            'reason' => 'Sakit demam surat dokter terlampir',
            'evidence_media_id' => $media->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        Carbon::setTestNow(Carbon::parse('2026-11-18 08:00:00', 'Asia/Jakarta'));

        // Intern mencoba check-in padahal izinnya sudah disetujui => ditolak
        $response = $this->actingAs($intern)->post('/intern/presensi', [
            'photo' => UploadedFile::fake()->image('selfie.jpg'),
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(0, Attendance::count());
    }

    public function test_admin_can_correct_attendance_status_with_notes_and_audit_log(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $media = MediaFile::create([
            'disk' => 'local_private',
            'file_path' => 'evidence/photo.jpg',
            'file_name' => 'photo.jpg',
            'original_name' => 'photo.jpg',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => 1024,
            'category' => 'attendance_photo',
            'owner_user_id' => $intern->id,
        ]);

        // Awalnya tercatat terlambat (late)
        $attendance = Attendance::create([
            'intern_user_id' => $intern->id,
            'attendance_date' => '2026-11-18',
            'checked_in_at' => '2026-11-18 08:15:00',
            'status' => 'late',
            'photo_media_id' => $media->id,
        ]);

        // Admin mengoreksi status menjadi on_time karena kendala sistem jaringan
        $response = $this->actingAs($admin)
            ->from('/admin/presensi')
            ->patch("/admin/presensi/{$attendance->id}", [
                'status' => 'on_time',
                'notes' => 'Koreksi status: peserta melapor kendala jaringan Wi-Fi kantor tepat pukul 08:00',
            ]);

        $response->assertRedirect('/admin/presensi');
        $response->assertSessionHas('success');

        $attendance->refresh();
        $this->assertEquals('on_time', $attendance->status);
        $this->assertEquals($admin->id, $attendance->reviewed_by);
        $this->assertStringContainsString('Koreksi status', $attendance->notes);

        // Pastikan audit log tercatat
        $this->assertDatabaseHas('audit_logs', [
            'actor_user_id' => $admin->id,
            'action' => 'attendance_corrected',
            'entity_type' => 'attendance',
            'entity_id' => $attendance->id,
        ]);

        $log = AuditLog::where('entity_type', 'attendance')->first();
        $this->assertNotNull($log);
        $this->assertEquals('late', $log->old_values['status']);
        $this->assertEquals('on_time', $log->new_values['status']);
    }

    public function test_pic_is_forbidden_from_checking_in_and_updating_attendance(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // A. PIC mencoba hit endpoint check-in peserta => 403
        $this->actingAs($pic)->post('/intern/presensi', [
            'photo' => UploadedFile::fake()->image('selfie.jpg'),
        ])->assertStatus(403);

        // B. PIC mencoba mengoreksi status presensi peserta => 403
        $media = MediaFile::create([
            'disk' => 'local_private',
            'file_path' => 'evidence/photo.jpg',
            'file_name' => 'photo.jpg',
            'original_name' => 'photo.jpg',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => 1024,
            'category' => 'attendance_photo',
            'owner_user_id' => $intern->id,
        ]);

        $attendance = Attendance::create([
            'intern_user_id' => $intern->id,
            'attendance_date' => '2026-11-18',
            'checked_in_at' => '2026-11-18 08:10:00',
            'status' => 'late',
            'photo_media_id' => $media->id,
        ]);

        $this->actingAs($pic)->patch("/admin/presensi/{$attendance->id}", [
            'status' => 'on_time',
            'notes' => 'PIC mencoba mengubah',
        ])->assertStatus(403);
    }
}
