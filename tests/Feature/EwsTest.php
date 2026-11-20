<?php

namespace Tests\Feature;

use App\Enums\LogbookSource;
use App\Enums\LogbookStatus;
use App\Enums\TaskStatus;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\CalendarException;
use App\Models\EwsSetting;
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

class EwsTest extends TestCase
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

    /**
     * 1. Akhir pekan (Sabtu/Minggu) dan hari libur operasional resmi tidak memicu EWS.
     */
    public function test_weekend_and_holiday_exceptions_do_not_trigger_ews(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'name' => 'Peserta Uji', 'is_active' => true]);

        // Skenario A: Sabtu (akhir pekan), jam 10:00 WIB
        Carbon::setTestNow(Carbon::parse('2026-11-21 10:00:00', 'Asia/Jakarta'));

        $responseWeekend = $this->actingAs($admin)->get('/admin/ews?date=2026-11-21');
        $responseWeekend->assertOk();
        $responseWeekend->assertSee('Hari Libur Operasional');
        $responseWeekend->assertSee('Semua Terkendali');

        // Skenario B: Hari Rabu (2026-11-18) yang ditetapkan sebagai libur di kalender
        CalendarException::create([
            'exception_date' => '2026-11-18',
            'type' => 'holiday',
            'name' => 'Hari Libur Nasional Tambahan',
            'description' => 'Libur resmi perusahaan',
            'created_by' => $admin->id,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00:00', 'Asia/Jakarta'));

        $responseHoliday = $this->actingAs($admin)->get('/admin/ews?date=2026-11-18');
        $responseHoliday->assertOk();
        $responseHoliday->assertSee('Hari Libur Operasional');
        $responseHoliday->assertSee('Semua Terkendali');
    }

    /**
     * 2. Override working_day pada akhir pekan memicu kewajiban presensi/logbook EWS.
     */
    public function test_working_day_override_on_weekend_triggers_ews_when_criteria_met(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'name' => 'Peserta Lembur', 'is_active' => true]);

        // Sabtu dijadikan hari kerja (working_day override)
        CalendarException::create([
            'exception_date' => '2026-11-21',
            'type' => 'working_day',
            'name' => 'Sabtu Masuk Pengganti Libur',
            'description' => 'Wajib presensi',
            'created_by' => $admin->id,
        ]);

        // Server pukul 10:00 WIB (melewati cutoff presensi 09:00 WIB)
        Carbon::setTestNow(Carbon::parse('2026-11-21 10:00:00', 'Asia/Jakarta'));

        $response = $this->actingAs($admin)->get('/admin/ews?date=2026-11-21');
        $response->assertOk();
        $response->assertSee('Peserta Lembur');
        $response->assertSee('BELUM PRESENSI');
        $response->assertDontSee('Hari Libur Operasional');
    }

    /**
     * 3. Sebelum jam cutoff hari ini sinyal belum terpicu; setelah melewati cutoff sinyal terpicu.
     */
    public function test_before_cutoff_time_today_signal_not_triggered_and_after_cutoff_triggered(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'name' => 'Peserta On-Duty', 'is_active' => true]);

        // Rabu hari kerja normal
        // 08:30 WIB: sebelum cutoff presensi (09:00 WIB)
        Carbon::setTestNow(Carbon::parse('2026-11-18 08:30:00', 'Asia/Jakarta'));

        $responsePagi = $this->actingAs($admin)->get('/admin/ews?date=2026-11-18');
        $responsePagi->assertOk();
        $responsePagi->assertSee('Semua Terkendali');
        $responsePagi->assertDontSee('BELUM PRESENSI');

        // 09:15 WIB: melewati cutoff presensi (09:00 WIB), tetapi belum cutoff logbook (17:00 WIB)
        Carbon::setTestNow(Carbon::parse('2026-11-18 09:15:00', 'Asia/Jakarta'));

        $responseSiang = $this->actingAs($admin)->get('/admin/ews?date=2026-11-18');
        $responseSiang->assertOk();
        $responseSiang->assertSee('BELUM PRESENSI');
        $responseSiang->assertDontSee('LOGBOOK KOSONG');

        // 17:15 WIB: melewati cutoff logbook (17:00 WIB)
        Carbon::setTestNow(Carbon::parse('2026-11-18 17:15:00', 'Asia/Jakarta'));

        $responseSore = $this->actingAs($admin)->get('/admin/ews?date=2026-11-18');
        $responseSore->assertOk();
        $responseSore->assertSee('BELUM PRESENSI');
        $responseSore->assertSee('LOGBOOK KOSONG');
    }

    /**
     * 4. Izin/sakit yang sudah disetujui (approved) dan akun nonaktif dikecualikan dari alarm EWS.
     */
    public function test_approved_leave_and_inactive_interns_do_not_trigger_ews(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);

        // Peserta A: Izin disetujui
        $internExcused = User::factory()->create(['role' => 'intern', 'name' => 'Peserta Izin Resmi', 'is_active' => true]);
        LeaveRequest::create([
            'intern_user_id' => $internExcused->id,
            'leave_type' => 'sick',
            'start_date' => '2026-11-18',
            'end_date' => '2026-11-18',
            'reason' => 'Demam tinggi istirahat dokter',
            'status' => 'approved',
        ]);

        // Peserta B: Akun nonaktif / alumni magang
        $internInactive = User::factory()->create(['role' => 'intern', 'name' => 'Alumni Nonaktif', 'is_active' => false]);
        // Tugas alumni yang overdue
        Task::create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $internInactive->id,
            'title' => 'Tugas Alumni Masa Lalu',
            'description' => 'Tugas lama yang tertinggal',
            'assigned_at' => '2026-11-01 09:00:00',
            'deadline_at' => '2026-11-05 17:00:00',
            'status' => TaskStatus::InProgress,
        ]);

        // Jam 18:00 WIB (melewati seluruh cutoff)
        Carbon::setTestNow(Carbon::parse('2026-11-18 18:00:00', 'Asia/Jakarta'));

        $response = $this->actingAs($admin)->get('/admin/ews?date=2026-11-18');
        $response->assertOk();
        $response->assertDontSee('Peserta Izin Resmi');
        $response->assertDontSee('Alumni Nonaktif');
        $response->assertDontSee('Tugas Alumni Masa Lalu');
    }

    /**
     * 5. Presensi yang ditolak (rejected) tetap dihitung belum presensi sah;
     * dan logbook task_assignment tidak memenuhi kewajiban logbook harian manual.
     */
    public function test_rejected_attendance_counts_as_unsubmitted_and_task_assignment_logbook_does_not_satisfy_manual_logbook(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $internA = User::factory()->create(['role' => 'intern', 'name' => 'Peserta Foto Ditolak', 'is_active' => true]);
        $internB = User::factory()->create(['role' => 'intern', 'name' => 'Peserta Hanya Tugas', 'is_active' => true]);
        $media = $this->createMedia($internA);

        Carbon::setTestNow(Carbon::parse('2026-11-18 18:00:00', 'Asia/Jakarta'));

        // Intern A presensi tapi ditolak HR
        Attendance::create([
            'intern_user_id' => $internA->id,
            'photo_media_id' => $media->id,
            'attendance_date' => '2026-11-18',
            'checked_in_at' => '2026-11-18 08:00:00',
            'status' => 'on_time',
            'verification_status' => 'rejected',
            'reviewed_by' => $admin->id,
            'notes' => 'Foto tidak valid',
        ]);

        // Intern B hanya punya logbook otomatis task_assignment (bukan manual)
        Logbook::create([
            'intern_user_id' => $internB->id,
            'log_date' => '2026-11-18',
            'title' => 'Tugas Otomatis Sistem',
            'activity_description' => 'Menerima tugas dari PIC',
            'source' => LogbookSource::TaskAssignment,
            'status' => LogbookStatus::Pending,
        ]);

        $response = $this->actingAs($admin)->get('/admin/ews?date=2026-11-18');
        $response->assertOk();
        $response->assertSee('Peserta Foto Ditolak');
        $response->assertSee('BELUM PRESENSI');
        $response->assertSee('Peserta Hanya Tugas');
        $response->assertSee('LOGBOOK KOSONG');
    }

    /**
     * 6. Tugas dengan perpanjangan deadline ke masa depan tidak dianggap overdue.
     */
    public function test_task_with_extended_deadline_in_future_is_not_overdue(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'name' => 'Peserta Tugas Aktif', 'is_active' => true]);

        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00:00', 'Asia/Jakarta'));

        // Tugas A: Deadline diperpanjang ke masa depan (2026-11-20 17:00:00)
        Task::create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'title' => 'Tugas Diperpanjang',
            'description' => 'Mendapat perpanjangan waktu dari PIC',
            'assigned_at' => '2026-11-15 09:00:00',
            'deadline_at' => '2026-11-20 17:00:00',
            'status' => TaskStatus::InProgress,
        ]);

        // Tugas B: Deadline sudah lewat kemarin dan belum accepted
        Task::create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'title' => 'Tugas Nyata Overdue',
            'description' => 'Terlambat diselesaikan',
            'assigned_at' => '2026-11-15 09:00:00',
            'deadline_at' => '2026-11-17 17:00:00',
            'status' => TaskStatus::InProgress,
        ]);

        $response = $this->actingAs($admin)->get('/admin/ews?type=task');
        $response->assertOk();
        $response->assertSee('Tugas Nyata Overdue');
        $response->assertDontSee('Tugas Diperpanjang');
    }

    /**
     * 7. Admin dapat mengubah pengaturan batas jam cutoff EWS dan tercatat di audit_logs.
     */
    public function test_admin_can_update_cutoff_settings_and_audit_log_is_recorded(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        // Cek initial default
        $initialSetting = EwsSetting::getSettings();
        $this->assertEquals('09:00', $initialSetting->attendance_cutoff_time);
        $this->assertEquals('17:00', $initialSetting->logbook_cutoff_time);

        // Update jam cutoff
        $response = $this->actingAs($admin)->put('/admin/ews/settings', [
            'attendance_cutoff_time' => '09:30',
            'logbook_cutoff_time' => '16:45',
        ]);

        $response->assertRedirect('/admin/ews');
        $response->assertSessionHas('success');

        // Pastikan tabel ews_settings berubah
        $updated = EwsSetting::first();
        $this->assertEquals('09:30', $updated->attendance_cutoff_time);
        $this->assertEquals('16:45', $updated->logbook_cutoff_time);
        $this->assertEquals($admin->id, $updated->updated_by);

        // Pastikan tercatat di audit_logs
        $this->assertDatabaseHas('audit_logs', [
            'actor_user_id' => $admin->id,
            'action' => 'ews_settings_updated',
            'entity_type' => 'ews_settings',
            'entity_id' => $updated->id,
        ]);

        // Validasi format salah harus ditolak
        $responseInvalid = $this->actingAs($admin)->put('/admin/ews/settings', [
            'attendance_cutoff_time' => '99:99',
            'logbook_cutoff_time' => 'invalid-time',
        ]);
        $responseInvalid->assertSessionHasErrors(['attendance_cutoff_time', 'logbook_cutoff_time']);
    }

    /**
     * 8. Otorisasi dan pembatasan cakupan EWS:
     * - PIC & Intern dilarang mengubah pengaturan cutoff (403).
     * - Intern dilarang mengakses halaman EWS admin maupun pic (403).
     * - PIC hanya dapat melihat peserta bimbingan dan tugas miliknya.
     */
    public function test_authorization_and_scoping_on_ews_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $pic1 = User::factory()->create(['role' => 'pic', 'name' => 'PIC Alfa', 'is_active' => true]);
        $pic2 = User::factory()->create(['role' => 'pic', 'name' => 'PIC Bravo', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // 1. Intern mencoba akses
        $this->actingAs($intern)->get('/admin/ews')->assertForbidden();
        $this->actingAs($intern)->get('/pic/ews')->assertForbidden();
        $this->actingAs($intern)->put('/admin/ews/settings', [
            'attendance_cutoff_time' => '09:00',
            'logbook_cutoff_time' => '17:00',
        ])->assertForbidden();

        // 2. PIC mencoba update settings
        $this->actingAs($pic1)->put('/admin/ews/settings', [
            'attendance_cutoff_time' => '09:00',
            'logbook_cutoff_time' => '17:00',
        ])->assertForbidden();

        // 3. Cakupan PIC: PIC 1 hanya melihat bimbingannya, tidak melihat bimbingan PIC 2
        $intern1 = User::factory()->create(['role' => 'intern', 'name' => 'Bimbingan Alfa', 'is_active' => true]);
        $intern2 = User::factory()->create(['role' => 'intern', 'name' => 'Bimbingan Bravo', 'is_active' => true]);

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

        // Tugas PIC 1 overdue
        Task::create([
            'pic_user_id' => $pic1->id,
            'intern_user_id' => $intern1->id,
            'title' => 'Tugas Milik PIC Alfa',
            'description' => 'Khusus Alfa',
            'assigned_at' => '2026-11-10 09:00:00',
            'deadline_at' => '2026-11-15 17:00:00',
            'status' => TaskStatus::InProgress,
        ]);

        // Tugas PIC 2 overdue
        Task::create([
            'pic_user_id' => $pic2->id,
            'intern_user_id' => $intern2->id,
            'title' => 'Tugas Rahasia PIC Bravo',
            'description' => 'Khusus Bravo',
            'assigned_at' => '2026-11-10 09:00:00',
            'deadline_at' => '2026-11-15 17:00:00',
            'status' => TaskStatus::InProgress,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00:00', 'Asia/Jakarta'));

        $responsePic1 = $this->actingAs($pic1)->get('/pic/ews');
        $responsePic1->assertOk();
        $responsePic1->assertSee('Bimbingan Alfa');
        $responsePic1->assertSee('Tugas Milik PIC Alfa');
        $responsePic1->assertDontSee('Bimbingan Bravo');
        $responsePic1->assertDontSee('Tugas Rahasia PIC Bravo');
    }
}
