<?php

namespace Tests\Feature;

use App\Enums\LogbookSource;
use App\Enums\LogbookStatus;
use App\Enums\TaskStatus;
use App\Models\Attendance;
use App\Models\CalendarException;
use App\Models\LeaveRequest;
use App\Models\Logbook;
use App\Models\MediaFile;
use App\Models\PicAssignment;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local_private');
    }

    protected function createMedia(User $user): MediaFile
    {
        return MediaFile::create([
            'owner_user_id' => $user->id,
            'category' => 'attendance_photo',
            'disk' => 'local_private',
            'file_path' => 'presensi/test_selfie.jpg',
            'original_name' => 'test_selfie.jpg',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => 1024,
            'uploaded_at' => now(),
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/pic/dashboard')->assertRedirect('/login');
        $this->get('/intern/dashboard')->assertRedirect('/login');
    }

    public function test_role_based_dashboard_authorization(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // Intern checks
        $this->actingAs($intern)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($intern)->get('/pic/dashboard')->assertForbidden();
        $this->actingAs($intern)->get('/intern/dashboard')->assertOk();

        // PIC checks
        $this->actingAs($pic)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($pic)->get('/intern/dashboard')->assertForbidden();
        $this->actingAs($pic)->get('/pic/dashboard')->assertOk();

        // Admin checks
        $this->actingAs($admin)->get('/intern/dashboard')->assertForbidden();
        $this->actingAs($admin)->get('/pic/dashboard')->assertForbidden();
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
    }

    public function test_inactive_accounts_are_rejected_and_redirected_to_login(): void
    {
        $inactiveAdmin = User::factory()->create(['role' => 'admin', 'is_active' => false]);
        $inactivePic = User::factory()->create(['role' => 'pic', 'is_active' => false]);
        $inactiveIntern = User::factory()->create(['role' => 'intern', 'is_active' => false]);

        $this->actingAs($inactiveAdmin)->get('/admin/dashboard')
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->actingAs($inactivePic)->get('/pic/dashboard')
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->actingAs($inactiveIntern)->get('/intern/dashboard')
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');
    }

    public function test_intern_dashboard_reflects_not_checked_in_on_working_day(): void
    {
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // Rabu hari kerja
        Carbon::setTestNow(Carbon::parse('2026-11-18 07:30:00', 'Asia/Jakarta'));

        $response = $this->actingAs($intern)->get('/intern/dashboard');
        $response->assertOk();
        $response->assertSee('Belum');
        $response->assertSee('Presensi Sekarang');
    }

    public function test_intern_dashboard_reflects_checked_in_attendance(): void
    {
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $media = $this->createMedia($intern);

        Carbon::setTestNow(Carbon::parse('2026-11-18 08:00:00', 'Asia/Jakarta'));

        Attendance::create([
            'intern_user_id' => $intern->id,
            'photo_media_id' => $media->id,
            'attendance_date' => '2026-11-18',
            'checked_in_at' => '2026-11-18 08:00:00',
            'status' => 'on_time',
        ]);

        $response = $this->actingAs($intern)->get('/intern/dashboard');
        $response->assertOk();
        $response->assertSee('Hadir');
        $response->assertSee('08:00 WIB');
        $response->assertSee('Tepat Waktu');
        $response->assertSee('Presensi Masuk Berhasil Tercatat');
    }

    public function test_intern_dashboard_reflects_rejected_attendance(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $media = $this->createMedia($intern);

        Carbon::setTestNow(Carbon::parse('2026-11-18 08:00:00', 'Asia/Jakarta'));

        Attendance::create([
            'intern_user_id' => $intern->id,
            'photo_media_id' => $media->id,
            'attendance_date' => '2026-11-18',
            'checked_in_at' => '2026-11-18 08:00:00',
            'status' => 'on_time',
            'verification_status' => 'rejected',
            'reviewed_by' => $admin->id,
            'notes' => 'Foto tidak jelas, silakan foto ulang di ruangan terang.',
        ]);

        $response = $this->actingAs($intern)->get('/intern/dashboard');
        $response->assertOk();
        $response->assertSee('Ditolak HR');
        $response->assertSee('Perlu Foto Ulang • Batas: Hari ini');
        $response->assertSee('Foto Presensi Masuk Ditolak HR');
        $response->assertSee('Foto tidak jelas, silakan foto ulang di ruangan terang.');
        $response->assertSee('Buka Kamera &amp; Foto Ulang', false);
    }

    public function test_intern_dashboard_reflects_holiday_status(): void
    {
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        CalendarException::create([
            'exception_date' => '2026-11-18',
            'type' => 'holiday',
            'name' => 'Hari Libur Khusus PT WAN',
            'description' => 'Ulang tahun perusahaan',
            'created_by' => $intern->id,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-11-18 08:00:00', 'Asia/Jakarta'));

        $response = $this->actingAs($intern)->get('/intern/dashboard');
        $response->assertOk();
        $response->assertSee('Libur');
        $response->assertSee('Hari Ini Libur Operasional');
        $response->assertSee('Hari Libur Khusus PT WAN');
    }

    public function test_intern_dashboard_reflects_approved_leave(): void
    {
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        LeaveRequest::create([
            'intern_user_id' => $intern->id,
            'leave_type' => 'sick',
            'start_date' => '2026-11-17',
            'end_date' => '2026-11-19',
            'reason' => 'Demam dan flu berat butuh istirahat',
            'status' => 'approved',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-11-18 08:00:00', 'Asia/Jakarta'));

        $response = $this->actingAs($intern)->get('/intern/dashboard');
        $response->assertOk();
        $response->assertSee('Sakit');
        $response->assertSee('Izin Resmi Disetujui Hari Ini');
    }

    public function test_admin_dashboard_displays_counts_and_attendance_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern1 = User::factory()->create(['role' => 'intern', 'name' => 'Peserta Satu', 'is_active' => true]);
        $intern2 = User::factory()->create(['role' => 'intern', 'name' => 'Peserta Dua', 'is_active' => true]);
        $media = $this->createMedia($intern1);

        Carbon::setTestNow(Carbon::parse('2026-11-18 08:00:00', 'Asia/Jakarta'));

        Attendance::create([
            'intern_user_id' => $intern1->id,
            'photo_media_id' => $media->id,
            'attendance_date' => '2026-11-18',
            'checked_in_at' => '2026-11-18 08:00:00',
            'status' => 'on_time',
        ]);

        LeaveRequest::create([
            'intern_user_id' => $intern2->id,
            'leave_type' => 'leave',
            'start_date' => '2026-11-20',
            'end_date' => '2026-11-21',
            'reason' => 'Urusan keluarga mendesak',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertOk();
        $response->assertSee('Peserta Satu');
        $response->assertSee('Tepat Waktu');
        $response->assertSee('Urusan keluarga mendesak');
        $response->assertSee('1 Menunggu');
    }

    public function test_admin_dashboard_shows_global_data_and_task_metrics(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'name' => 'Global Intern', 'is_active' => true]);

        Carbon::setTestNow(Carbon::parse('2026-11-18 09:00:00', 'Asia/Jakarta'));

        // Buat tugas yang sedang menunggu review
        Task::create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'title' => 'Global Task Review Testing',
            'description' => 'Deskripsi testing tugas',
            'assigned_at' => now(),
            'deadline_at' => now()->addDays(2),
            'status' => TaskStatus::Submitted,
        ]);

        // Buat logbook manual terbaru
        Logbook::create([
            'intern_user_id' => $intern->id,
            'log_date' => '2026-11-18',
            'title' => 'Logbook Harian Mandiri Global',
            'activity_description' => 'Mengerjakan integrasi modul dashboard',
            'source' => LogbookSource::Manual,
            'status' => LogbookStatus::Pending,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertOk();
        $response->assertSee('Logbook Harian Mandiri Global');
        $response->assertSee('Tugas Menunggu Review');
    }

    public function test_admin_dashboard_search_filter_functions(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $internA = User::factory()->create(['role' => 'intern', 'name' => 'Alfa Romeo', 'email' => 'alfa@example.com', 'is_active' => true]);
        $internB = User::factory()->create(['role' => 'intern', 'name' => 'Bravo Delta', 'email' => 'bravo@example.com', 'is_active' => true]);

        $mediaA = $this->createMedia($internA);
        $mediaB = $this->createMedia($internB);

        Carbon::setTestNow(Carbon::parse('2026-11-18 08:00:00', 'Asia/Jakarta'));

        Attendance::create([
            'intern_user_id' => $internA->id,
            'photo_media_id' => $mediaA->id,
            'attendance_date' => '2026-11-18',
            'checked_in_at' => '2026-11-18 08:00:00',
            'status' => 'on_time',
        ]);

        Attendance::create([
            'intern_user_id' => $internB->id,
            'photo_media_id' => $mediaB->id,
            'attendance_date' => '2026-11-18',
            'checked_in_at' => '2026-11-18 08:04:00',
            'status' => 'on_time',
        ]);

        // Cari dengan kata kunci "Alfa"
        $response = $this->actingAs($admin)->get('/admin/dashboard?search=Alfa');
        $response->assertOk();
        $response->assertSee('Alfa Romeo');
        $response->assertDontSee('Bravo Delta');
    }

    public function test_pic_dashboard_shows_only_assigned_interns(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $assignedIntern = User::factory()->create(['role' => 'intern', 'name' => 'Bimbingan PIC', 'is_active' => true]);
        $otherIntern = User::factory()->create(['role' => 'intern', 'name' => 'Intern Lain', 'is_active' => true]);
        $media = $this->createMedia($assignedIntern);

        PicAssignment::create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $assignedIntern->id,
            'assigned_by' => $admin->id,
            'start_date' => '2026-11-01',
            'end_date' => null, // Aktif
        ]);

        Carbon::setTestNow(Carbon::parse('2026-11-18 08:00:00', 'Asia/Jakarta'));

        Attendance::create([
            'intern_user_id' => $assignedIntern->id,
            'photo_media_id' => $media->id,
            'attendance_date' => '2026-11-18',
            'checked_in_at' => '2026-11-18 07:55:00',
            'status' => 'on_time',
        ]);

        $response = $this->actingAs($pic)->get('/pic/dashboard');
        $response->assertOk();
        $response->assertSee('Presensi &amp; Izin Anak Bimbingan Hari Ini', false);
        $response->assertSee('Bimbingan PIC');
        $response->assertDontSee('Intern Lain');
    }

    public function test_pic_dashboard_strictly_scopes_tasks_and_does_not_leak_other_pic_tasks(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $pic1 = User::factory()->create(['role' => 'pic', 'name' => 'PIC Utama', 'is_active' => true]);
        $pic2 = User::factory()->create(['role' => 'pic', 'name' => 'PIC Sebelah', 'is_active' => true]);

        $intern1 = User::factory()->create(['role' => 'intern', 'name' => 'Peserta Satu', 'is_active' => true]);
        $intern2 = User::factory()->create(['role' => 'intern', 'name' => 'Peserta Dua', 'is_active' => true]);

        PicAssignment::create([
            'pic_user_id' => $pic1->id,
            'intern_user_id' => $intern1->id,
            'assigned_by' => $admin->id,
            'start_date' => '2026-11-01',
            'end_date' => null,
        ]);

        PicAssignment::create([
            'pic_user_id' => $pic2->id,
            'intern_user_id' => $intern2->id,
            'assigned_by' => $admin->id,
            'start_date' => '2026-11-01',
            'end_date' => null,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00:00', 'Asia/Jakarta'));

        // Tugas PIC 1
        Task::create([
            'pic_user_id' => $pic1->id,
            'intern_user_id' => $intern1->id,
            'title' => 'Tugas Spesifik PIC Utama',
            'description' => 'Deskripsi tugas milik PIC 1',
            'assigned_at' => now(),
            'deadline_at' => now()->addDays(2),
            'status' => TaskStatus::Submitted,
        ]);

        // Tugas PIC 2 (tidak boleh bocor ke PIC 1)
        Task::create([
            'pic_user_id' => $pic2->id,
            'intern_user_id' => $intern2->id,
            'title' => 'Tugas Rahasia PIC Sebelah',
            'description' => 'Deskripsi tugas milik PIC 2',
            'assigned_at' => now(),
            'deadline_at' => now()->addDays(2),
            'status' => TaskStatus::Submitted,
        ]);

        $response = $this->actingAs($pic1)->get('/pic/dashboard');
        $response->assertOk();
        $response->assertSee('Tugas Spesifik PIC Utama');
        $response->assertDontSee('Tugas Rahasia PIC Sebelah');
    }
}
