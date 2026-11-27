<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\CalendarException;
use App\Models\LeaveRequest;
use App\Models\MediaFile;
use App\Models\PicAssignment;
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
            'device_type' => 'desktop',
            'browser' => 'Chrome',
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

        // Override jadi libur khusus
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
            'device_type' => 'desktop',
            'browser' => 'Chrome',
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
        $response->assertSee('Jam Masuk');
        $response->assertSee('Foto Bukti');
        $response->assertSee('PIC Mentor Aktif');
        $response->assertSee('Pengajuan Izin &amp; Sakit', false);
    }

    public function test_pic_dashboard_does_not_show_attendance_or_leave_widget(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);

        $response = $this->actingAs($pic)->get('/pic/dashboard');
        $response->assertOk();
        $response->assertSee('Dashboard Bimbingan PIC');
        $response->assertDontSee('Presensi &amp; Izin Anak Bimbingan Hari Ini', false);
        $response->assertDontSee('Presensi Bimbingan');
        $response->assertDontSee('Izin Bimbingan');
    }
}
