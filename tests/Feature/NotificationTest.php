<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\MediaFile;
use App\Models\User;
use App\Notifications\AttendanceCorrectedNotification;
use App\Notifications\AttendanceRejectedNotification;
use App\Notifications\AttendanceSubmittedNotification;
use App\Notifications\LeaveRequestReviewedNotification;
use App\Notifications\LeaveRequestSubmittedNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local_private');
        Carbon::setTestNow(Carbon::parse('2026-11-27 07:55:00', 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_active_admins_receive_notification_on_intern_checkin_and_resubmission(): void
    {
        $activeAdmin1 = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $activeAdmin2 = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $inactiveAdmin = User::factory()->create(['role' => 'admin', 'is_active' => false]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true, 'name' => 'Budi Santoso']);
        $otherIntern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);

        // 1. Initial check-in
        $this->actingAs($intern)->post('/intern/presensi', [
            'photo' => UploadedFile::fake()->image('selfie1.jpg'),
        ])->assertRedirect('/intern/presensi');

        $this->assertEquals(1, $activeAdmin1->unreadNotifications()->count());
        $this->assertEquals(1, $activeAdmin2->unreadNotifications()->count());
        $this->assertEquals(0, $inactiveAdmin->notifications()->count());
        $this->assertEquals(0, $otherIntern->notifications()->count());
        $this->assertEquals(0, $pic->notifications()->count());

        $notif = $activeAdmin1->unreadNotifications()->first();
        $this->assertEquals(AttendanceSubmittedNotification::class, $notif->type);
        $this->assertEquals('Presensi Masuk Baru', $notif->data['title']);
        $this->assertStringContainsString('Budi Santoso', $notif->data['message']);

        // 2. Reject and resubmit (advance clock so created_at ordering is deterministic)
        $attendance = Attendance::where('intern_user_id', $intern->id)->firstOrFail();
        $this->actingAs($activeAdmin1)->post("/admin/presensi/{$attendance->id}/reject", [
            'notes' => 'Foto buram tidak terlihat.',
        ])->assertRedirect();

        Carbon::setTestNow(Carbon::parse('2026-11-27 08:00:00', 'Asia/Jakarta'));

        $this->actingAs($intern)->post('/intern/presensi', [
            'photo' => UploadedFile::fake()->image('selfie2.jpg'),
        ])->assertRedirect('/intern/presensi');

        $this->assertEquals(2, $activeAdmin1->unreadNotifications()->count());
        $latestAdminNotif = $activeAdmin1->unreadNotifications()->latest()->first();
        $this->assertEquals('Foto Presensi Ulang Dikirim', $latestAdminNotif->data['title']);
    }

    public function test_active_admins_receive_notification_on_leave_submission(): void
    {
        $activeAdmin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $inactiveAdmin = User::factory()->create(['role' => 'admin', 'is_active' => false]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true, 'name' => 'Siti Aminah']);
        $otherIntern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $this->actingAs($intern)->post('/intern/izin', [
            'leave_type' => 'sick',
            'start_date' => '2026-11-27',
            'end_date' => '2026-11-28',
            'reason' => 'Demam tinggi butuh istirahat',
            'evidence' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        ])->assertRedirect('/intern/izin');

        $this->assertEquals(1, $activeAdmin->unreadNotifications()->count());
        $this->assertEquals(0, $inactiveAdmin->notifications()->count());
        $this->assertEquals(0, $otherIntern->notifications()->count());

        $notif = $activeAdmin->unreadNotifications()->first();
        $this->assertEquals(LeaveRequestSubmittedNotification::class, $notif->type);
        $this->assertEquals('Pengajuan Sakit Baru', $notif->data['title']);
        $this->assertStringContainsString('Siti Aminah', $notif->data['message']);
    }

    public function test_only_owner_intern_receives_notification_when_hr_rejects_or_corrects_attendance(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true, 'name' => 'HR Admin']);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $otherIntern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);

        $media = MediaFile::create([
            'disk' => 'local_private',
            'file_path' => 'presensi/test.jpg',
            'file_name' => 'test.jpg',
            'original_name' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => 1024,
            'category' => 'attendance_photo',
            'owner_user_id' => $intern->id,
        ]);

        $attendance = Attendance::create([
            'intern_user_id' => $intern->id,
            'attendance_date' => '2026-11-27',
            'checked_in_at' => '2026-11-27 08:10:00',
            'status' => 'late',
            'verification_status' => 'submitted',
            'photo_media_id' => $media->id,
        ]);

        // Correct status
        $this->actingAs($admin)->patch("/admin/presensi/{$attendance->id}", [
            'status' => 'on_time',
            'notes' => 'Kendala jaringan kantor.',
        ])->assertRedirect();

        $this->assertEquals(1, $intern->unreadNotifications()->count());
        $this->assertEquals(0, $otherIntern->notifications()->count());
        $this->assertEquals(0, $pic->notifications()->count());

        $corrNotif = $intern->unreadNotifications()->first();
        $this->assertEquals(AttendanceCorrectedNotification::class, $corrNotif->type);
        $this->assertStringContainsString('Tepat Waktu', $corrNotif->data['message']);

        // Reject attendance
        $this->actingAs($admin)->post("/admin/presensi/{$attendance->id}/reject", [
            'notes' => 'Wajah tidak terlihat.',
        ])->assertRedirect();

        $this->assertEquals(2, $intern->unreadNotifications()->count());
        $rejNotif = $intern->unreadNotifications()->where('type', AttendanceRejectedNotification::class)->first();
        $this->assertNotNull($rejNotif);
        $this->assertStringContainsString('Wajah tidak terlihat.', $rejNotif->data['message']);
    }

    public function test_only_applicant_intern_receives_notification_when_hr_reviews_leave(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true, 'name' => 'HR Manager']);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $otherIntern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);

        $leave = LeaveRequest::create([
            'intern_user_id' => $intern->id,
            'leave_type' => 'leave',
            'start_date' => '2026-11-28',
            'end_date' => '2026-11-28',
            'reason' => 'Urusan keluarga',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post("/admin/izin/{$leave->id}/review", [
            'decision' => 'approved',
            'review_notes' => 'Disetujui sesuai prosedur.',
        ])->assertRedirect();

        $this->assertEquals(1, $intern->unreadNotifications()->count());
        $this->assertEquals(0, $otherIntern->notifications()->count());
        $this->assertEquals(0, $pic->notifications()->count());

        $notif = $intern->unreadNotifications()->first();
        $this->assertEquals(LeaveRequestReviewedNotification::class, $notif->type);
        $this->assertEquals('Pengajuan Izin/Sakit Disetujui', $notif->data['title']);
        $this->assertStringContainsString('Disetujui sesuai prosedur.', $notif->data['message']);
    }

    public function test_user_can_mark_own_notification_as_read_and_cannot_access_other_users_notification(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $internA = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $internB = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $leave = LeaveRequest::create([
            'intern_user_id' => $internA->id,
            'leave_type' => 'leave',
            'start_date' => '2026-11-28',
            'end_date' => '2026-11-28',
            'reason' => 'Keperluan pribadi',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post("/admin/izin/{$leave->id}/review", [
            'decision' => 'rejected',
            'review_notes' => 'Lampiran tidak lengkap.',
        ])->assertRedirect();

        $notifA = $internA->unreadNotifications()->firstOrFail();

        // Intern B tries to mark Intern A's notification as read => 404
        $this->actingAs($internB)
            ->post("/notifications/{$notifA->id}/read")
            ->assertStatus(404);

        $this->assertNull($notifA->fresh()->read_at);

        // Intern A marks own notification as read => redirects to action_url & sets read_at
        $this->actingAs($internA)
            ->post("/notifications/{$notifA->id}/read")
            ->assertRedirect($notifA->data['action_url']);

        $this->assertNotNull($notifA->fresh()->read_at);
        $this->assertEquals(0, $internA->unreadNotifications()->count());
    }

    public function test_mark_all_as_read_and_bell_dropdown_badge_render_accurately(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true, 'name' => 'Rina Putri']);

        $this->actingAs($intern)->post('/intern/izin', [
            'leave_type' => 'leave',
            'start_date' => '2026-11-29',
            'end_date' => '2026-11-29',
            'reason' => 'Keperluan akademik',
            'evidence' => UploadedFile::fake()->create('dispensasi.pdf', 100, 'application/pdf'),
        ])->assertRedirect('/intern/izin');

        $adminDash = $this->actingAs($admin)->get('/admin/dashboard');
        $adminDash->assertOk();
        $adminDash->assertSee('header-notification-badge');
        $adminDash->assertSee('Pengajuan Izin Baru');
        $adminDash->assertSee('Tandai Semua Dibaca');

        $this->actingAs($admin)
            ->from('/notifications')
            ->post('/notifications/read-all')
            ->assertRedirect('/notifications');

        $this->assertEquals(0, $admin->unreadNotifications()->count());

        $notifPage = $this->actingAs($admin)->get('/notifications');
        $notifPage->assertOk();
        $notifPage->assertSee('Semua notifikasi sudah dibaca');
    }

    public function test_notifications_feed_returns_only_authenticated_users_notifications_and_blocks_guests(): void
    {
        $this->getJson('/notifications/feed')->assertStatus(401);

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $internA = User::factory()->create(['role' => 'intern', 'is_active' => true, 'name' => 'Peserta A']);
        $internB = User::factory()->create(['role' => 'intern', 'is_active' => true, 'name' => 'Peserta B']);

        $leaveA = LeaveRequest::create([
            'intern_user_id' => $internA->id,
            'leave_type' => 'leave',
            'start_date' => '2026-11-28',
            'end_date' => '2026-11-28',
            'reason' => 'Izin Peserta A',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post("/admin/izin/{$leaveA->id}/review", [
            'decision' => 'approved',
            'review_notes' => 'Disetujui untuk A',
        ])->assertRedirect();

        // Intern A sees their notification in feed
        $feedA = $this->actingAs($internA)->getJson('/notifications/feed');
        $feedA->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('unread_db_count', 1)
            ->assertJsonFragment(['title' => 'Pengajuan Izin/Sakit Disetujui']);

        // Intern B sees 0 notifications and does not see Intern A's notification
        $feedB = $this->actingAs($internB)->getJson('/notifications/feed');
        $feedB->assertOk()
            ->assertJsonPath('unread_count', 0)
            ->assertJsonPath('unread_db_count', 0)
            ->assertJsonMissing(['title' => 'Pengajuan Izin/Sakit Disetujui']);
    }
}
