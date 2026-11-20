<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\CalendarException;
use App\Models\LeaveRequest;
use App\Models\MediaFile;
use App\Models\PicAssignment;
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

    public function test_admin_can_reject_attendance_with_reason(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $media = MediaFile::create([
            'disk' => 'local_private',
            'file_path' => 'presensi/photo.jpg',
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
            'checked_in_at' => '2026-11-18 08:02:00',
            'status' => 'on_time',
            'verification_status' => 'submitted',
            'photo_media_id' => $media->id,
        ]);

        $response = $this->actingAs($admin)
            ->from('/admin/presensi')
            ->post("/admin/presensi/{$attendance->id}/reject", [
                'notes' => 'Foto blur, wajah peserta tidak terlihat jelas.',
            ]);

        $response->assertRedirect('/admin/presensi');
        $response->assertSessionHas('success');

        $attendance->refresh();
        $this->assertEquals('rejected', $attendance->verification_status);
        $this->assertTrue($attendance->isRejected());
        $this->assertEquals('on_time', $attendance->status); // Original status preserved
        $this->assertEquals($admin->id, $attendance->reviewed_by);
        $this->assertEquals('Foto blur, wajah peserta tidak terlihat jelas.', $attendance->notes);

        // Audit log
        $this->assertDatabaseHas('audit_logs', [
            'actor_user_id' => $admin->id,
            'action' => 'attendance_rejected',
            'entity_type' => 'attendance',
            'entity_id' => $attendance->id,
        ]);
    }

    public function test_non_admin_cannot_reject_attendance(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $media = MediaFile::create([
            'disk' => 'local_private',
            'file_path' => 'presensi/photo.jpg',
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
            'checked_in_at' => '2026-11-18 08:02:00',
            'status' => 'on_time',
            'verification_status' => 'submitted',
            'photo_media_id' => $media->id,
        ]);

        // PIC cannot reject
        $this->actingAs($pic)
            ->post("/admin/presensi/{$attendance->id}/reject", [
                'notes' => 'PIC mencoba menolak presensi.',
            ])
            ->assertStatus(403);

        // Intern cannot reject
        $this->actingAs($intern)
            ->post("/admin/presensi/{$attendance->id}/reject", [
                'notes' => 'Intern mencoba menolak presensi.',
            ])
            ->assertStatus(403);
    }

    public function test_rejection_requires_notes_with_minimum_length(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $media = MediaFile::create([
            'disk' => 'local_private',
            'file_path' => 'presensi/photo.jpg',
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
            'checked_in_at' => '2026-11-18 08:02:00',
            'status' => 'on_time',
            'verification_status' => 'submitted',
            'photo_media_id' => $media->id,
        ]);

        // Empty notes
        $this->actingAs($admin)
            ->post("/admin/presensi/{$attendance->id}/reject", [
                'notes' => '',
            ])
            ->assertSessionHasErrors('notes');

        // Too short (< 5 chars)
        $this->actingAs($admin)
            ->post("/admin/presensi/{$attendance->id}/reject", [
                'notes' => 'Foto',
            ])
            ->assertSessionHasErrors('notes');
    }

    public function test_intern_can_resubmit_photo_on_same_day_after_rejection(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // 1. Initial check in at 08:02 (on_time)
        Carbon::setTestNow(Carbon::parse('2026-11-18 08:02:00', 'Asia/Jakarta'));
        $firstPhoto = UploadedFile::fake()->image('selfie_first.jpg');

        $this->actingAs($intern)->post('/intern/presensi', [
            'photo' => $firstPhoto,
        ])->assertSessionHas('success');

        $attendance = Attendance::where('intern_user_id', $intern->id)->first();
        $this->assertNotNull($attendance);
        $this->assertEquals('on_time', $attendance->status);
        $this->assertEquals('08:02:00', $attendance->checked_in_at->format('H:i:s'));
        $firstMediaId = $attendance->photo_media_id;
        $firstMedia = MediaFile::find($firstMediaId);

        // 2. Admin rejects at 08:30
        Carbon::setTestNow(Carbon::parse('2026-11-18 08:30:00', 'Asia/Jakarta'));
        $this->actingAs($admin)->post("/admin/presensi/{$attendance->id}/reject", [
            'notes' => 'Foto buram, harap foto ulang di ruangan terang.',
        ])->assertSessionHas('success');

        $attendance->refresh();
        $this->assertEquals('rejected', $attendance->verification_status);

        // 3. Intern sees camera re-opened on /intern/presensi
        $getRes = $this->actingAs($intern)->get('/intern/presensi');
        $getRes->assertOk();
        $getRes->assertSee('Foto Presensi Masuk Hari Ini Ditolak oleh HR');
        $getRes->assertSee('Foto buram, harap foto ulang di ruangan terang.');
        $getRes->assertSee('Ambil Foto Selfie Ulang');

        // 4. Intern resubmits at 09:15 on same day
        Carbon::setTestNow(Carbon::parse('2026-11-18 09:15:00', 'Asia/Jakarta'));
        $secondPhoto = UploadedFile::fake()->image('selfie_second.jpg');

        $resubmitRes = $this->actingAs($intern)->post('/intern/presensi', [
            'photo' => $secondPhoto,
            'notes' => 'Foto ulang dengan pencahayaan jelas',
        ]);
        $resubmitRes->assertSessionHas('success');

        // 5. Verification of business rules:
        // - Unique constraint preserved: only 1 attendance record exists
        $this->assertEquals(1, Attendance::where('intern_user_id', $intern->id)->whereDate('attendance_date', '2026-11-18')->count());

        $attendance->refresh();
        // - Original check-in time and status preserved!
        $this->assertEquals('08:02:00', $attendance->checked_in_at->format('H:i:s'));
        $this->assertEquals('on_time', $attendance->status);
        // - Status is submitted again
        $this->assertEquals('submitted', $attendance->verification_status);
        $this->assertFalse($attendance->isRejected());
        // - New photo media assigned
        $this->assertNotEquals($firstMediaId, $attendance->photo_media_id);
        // - Old photo record & file NEVER deleted
        $this->assertDatabaseHas('media_files', ['id' => $firstMediaId]);
        Storage::disk('local_private')->assertExists($firstMedia->file_path);

        // - Audit log records attendance_resubmitted
        $this->assertDatabaseHas('audit_logs', [
            'actor_user_id' => $intern->id,
            'action' => 'attendance_resubmitted',
            'entity_type' => 'attendance',
            'entity_id' => $attendance->id,
        ]);

        $resubmitLog = AuditLog::where('action', 'attendance_resubmitted')->first();
        $this->assertNotNull($resubmitLog);
        $this->assertEquals($firstMediaId, $resubmitLog->old_values['photo_media_id']);
        $this->assertEquals($attendance->photo_media_id, $resubmitLog->new_values['photo_media_id']);
    }

    public function test_past_rejection_does_not_open_today_camera(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $media = MediaFile::create([
            'disk' => 'local_private',
            'file_path' => 'presensi/past_photo.jpg',
            'file_name' => 'past_photo.jpg',
            'original_name' => 'past_photo.jpg',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => 1024,
            'category' => 'attendance_photo',
            'owner_user_id' => $intern->id,
        ]);

        // Kemarin (2026-11-17) ditolak oleh HR
        $yesterdayAttendance = Attendance::create([
            'intern_user_id' => $intern->id,
            'attendance_date' => '2026-11-17',
            'checked_in_at' => '2026-11-17 08:00:00',
            'status' => 'on_time',
            'verification_status' => 'rejected',
            'reviewed_by' => $admin->id,
            'notes' => 'Kemarin ditolak HR',
            'photo_media_id' => $media->id,
        ]);

        // Hari ini (2026-11-18) sudah presensi masuk (submitted)
        Carbon::setTestNow(Carbon::parse('2026-11-18 08:00:00', 'Asia/Jakarta'));
        $todayMedia = MediaFile::create([
            'disk' => 'local_private',
            'file_path' => 'presensi/today_photo.jpg',
            'file_name' => 'today_photo.jpg',
            'original_name' => 'today_photo.jpg',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => 1024,
            'category' => 'attendance_photo',
            'owner_user_id' => $intern->id,
        ]);
        $todayAttendance = Attendance::create([
            'intern_user_id' => $intern->id,
            'attendance_date' => '2026-11-18',
            'checked_in_at' => '2026-11-18 08:00:00',
            'status' => 'on_time',
            'verification_status' => 'submitted',
            'photo_media_id' => $todayMedia->id,
        ]);

        // Saat intern membuka presensi hari ini, kamera TIDAK terbuka (Kondisi 1 aktif: sudah check-in)
        $response = $this->actingAs($intern)->get('/intern/presensi');
        $response->assertOk();
        $response->assertSee('Presensi Masuk Berhasil Tercatat');
        $response->assertDontSee('Foto Presensi Masuk Hari Ini Ditolak oleh HR');
        // Riwayat menampilkan penolakan kemarin
        $response->assertSee('Kemarin ditolak HR');
    }

    public function test_recap_service_excludes_rejected_attendance_from_valid_present(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern1 = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $intern2 = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $media1 = MediaFile::create([
            'disk' => 'local_private',
            'file_path' => 'presensi/photo1.jpg',
            'file_name' => 'photo1.jpg',
            'original_name' => 'photo1.jpg',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => 1024,
            'category' => 'attendance_photo',
            'owner_user_id' => $intern1->id,
        ]);
        $media2 = MediaFile::create([
            'disk' => 'local_private',
            'file_path' => 'presensi/photo2.jpg',
            'file_name' => 'photo2.jpg',
            'original_name' => 'photo2.jpg',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => 1024,
            'category' => 'attendance_photo',
            'owner_user_id' => $intern2->id,
        ]);

        // intern 1: valid submitted on_time
        Attendance::create([
            'intern_user_id' => $intern1->id,
            'attendance_date' => '2026-11-18',
            'checked_in_at' => '2026-11-18 08:00:00',
            'status' => 'on_time',
            'verification_status' => 'submitted',
            'photo_media_id' => $media1->id,
        ]);

        // intern 2: rejected
        Attendance::create([
            'intern_user_id' => $intern2->id,
            'attendance_date' => '2026-11-18',
            'checked_in_at' => '2026-11-18 08:00:00',
            'status' => 'on_time',
            'verification_status' => 'rejected',
            'reviewed_by' => $admin->id,
            'notes' => 'Selfie buram',
            'photo_media_id' => $media2->id,
        ]);

        $recapService = app(\App\Services\AttendanceRecapService::class);
        $daily = $recapService->getDailyOverallRecap('2026-11-18');

        // Only intern 1 counted as present
        $this->assertEquals(1, $daily['total_present']);
        $this->assertEquals(1, $daily['on_time_count']);
        $this->assertEquals(1, $daily['rejected_count']);
    }

    public function test_admin_can_view_attendance_index_with_populated_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern1 = User::factory()->create(['name' => 'Peserta Satu', 'role' => 'intern', 'is_active' => true]);
        $intern2 = User::factory()->create(['name' => 'Peserta Dua', 'role' => 'intern', 'is_active' => true]);

        $media1 = MediaFile::create([
            'disk' => 'local_private',
            'file_path' => 'presensi/photo1.jpg',
            'file_name' => 'photo1.jpg',
            'original_name' => 'photo1.jpg',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => 1024,
            'category' => 'attendance_photo',
            'owner_user_id' => $intern1->id,
        ]);
        $media2 = MediaFile::create([
            'disk' => 'local_private',
            'file_path' => 'presensi/photo2.jpg',
            'file_name' => 'photo2.jpg',
            'original_name' => 'photo2.jpg',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => 1024,
            'category' => 'attendance_photo',
            'owner_user_id' => $intern2->id,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-11-18 09:00:00', 'Asia/Jakarta'));

        // Presensi 1: on_time dan sudah direview
        Attendance::create([
            'intern_user_id' => $intern1->id,
            'attendance_date' => '2026-11-18',
            'checked_in_at' => '2026-11-18 08:00:00',
            'status' => 'on_time',
            'verification_status' => 'submitted',
            'reviewed_by' => $admin->id,
            'notes' => 'Catatan review admin',
            'photo_media_id' => $media1->id,
        ]);

        // Presensi 2: rejected
        Attendance::create([
            'intern_user_id' => $intern2->id,
            'attendance_date' => '2026-11-18',
            'checked_in_at' => '2026-11-18 08:15:00',
            'status' => 'late',
            'verification_status' => 'rejected',
            'reviewed_by' => $admin->id,
            'notes' => 'Wajah tidak tampak jelas',
            'photo_media_id' => $media2->id,
        ]);

        $response = $this->actingAs($admin)->get('/admin/presensi?date=2026-11-18');

        $response->assertOk();
        $response->assertSee('Peserta Satu');
        $response->assertSee('Peserta Dua');
        $response->assertSee('Tepat Waktu');
        $response->assertSee('Ditolak HR');
        $response->assertSee('Foto');
        $response->assertSee('Koreksi');
        $response->assertSee('Perlu Foto Ulang');
    }

    public function test_pic_can_view_assigned_interns_attendance_with_data(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $assignedIntern = User::factory()->create(['name' => 'Peserta Binaan A', 'role' => 'intern', 'is_active' => true]);
        $unassignedIntern = User::factory()->create(['name' => 'Peserta Lain B', 'role' => 'intern', 'is_active' => true]);

        PicAssignment::create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $assignedIntern->id,
            'start_date' => '2026-11-01',
            'end_date' => null,
            'assigned_by' => $pic->id,
        ]);

        $media = MediaFile::create([
            'disk' => 'local_private',
            'file_path' => 'presensi/binaan.jpg',
            'file_name' => 'binaan.jpg',
            'original_name' => 'binaan.jpg',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => 1024,
            'category' => 'attendance_photo',
            'owner_user_id' => $assignedIntern->id,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-11-18 09:00:00', 'Asia/Jakarta'));

        Attendance::create([
            'intern_user_id' => $assignedIntern->id,
            'attendance_date' => '2026-11-18',
            'checked_in_at' => '2026-11-18 08:04:00',
            'status' => 'on_time',
            'verification_status' => 'submitted',
            'notes' => 'Presensi hadir binaan',
            'photo_media_id' => $media->id,
        ]);

        $response = $this->actingAs($pic)->get('/pic/presensi?date=2026-11-18');

        $response->assertOk();
        $response->assertSee('Peserta Binaan A');
        $response->assertSee('Tepat Waktu');
        $response->assertSee('Lihat Foto');
        $response->assertDontSee('Peserta Lain B');
    }

    public function test_intern_can_view_attendance_index_with_populated_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $media = MediaFile::create([
            'disk' => 'local_private',
            'file_path' => 'presensi/history.jpg',
            'file_name' => 'history.jpg',
            'original_name' => 'history.jpg',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => 1024,
            'category' => 'attendance_photo',
            'owner_user_id' => $intern->id,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00:00', 'Asia/Jakarta'));

        Attendance::create([
            'intern_user_id' => $intern->id,
            'attendance_date' => '2026-11-17',
            'checked_in_at' => '2026-11-17 08:12:00',
            'status' => 'late',
            'verification_status' => 'submitted',
            'notes' => 'Terlambat karena hujan deras',
            'photo_media_id' => $media->id,
        ]);

        $response = $this->actingAs($intern)->get('/intern/presensi');

        $response->assertOk();
        $response->assertSee('Riwayat Presensi Anda');
        $response->assertSee('Terlambat');
        $response->assertSee('Terlambat karena hujan deras');
    }
}
