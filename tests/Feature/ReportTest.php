<?php

namespace Tests\Feature;

use App\Enums\LogbookSource;
use App\Enums\LogbookStatus;
use App\Enums\TaskStatus;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\LeaveRequest;
use App\Models\Logbook;
use App\Models\MediaFile;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportTest extends TestCase
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
     * 1. Otorisasi: Hanya Admin/HR yang boleh mengakses halaman dan ekspor laporan.
     * PIC dan Intern wajib mendapatkan respon 403 Forbidden.
     */
    public function test_pic_and_intern_are_forbidden_from_reports_module(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // PIC
        $this->actingAs($pic)->get('/admin/laporan')->assertForbidden();
        $this->actingAs($pic)->get('/admin/laporan/export-pdf?report_type=attendance&start_date=2026-11-01&end_date=2026-11-30')->assertForbidden();
        $this->actingAs($pic)->get('/admin/laporan/export-excel?report_type=attendance&start_date=2026-11-01&end_date=2026-11-30')->assertForbidden();

        // Intern
        $this->actingAs($intern)->get('/admin/laporan')->assertForbidden();
        $this->actingAs($intern)->get('/admin/laporan/export-pdf?report_type=attendance&start_date=2026-11-01&end_date=2026-11-30')->assertForbidden();
        $this->actingAs($intern)->get('/admin/laporan/export-excel?report_type=attendance&start_date=2026-11-01&end_date=2026-11-30')->assertForbidden();
    }

    /**
     * 2. Admin dapat mengakses halaman laporan dan melihat preview kelima jenis laporan.
     */
    public function test_admin_can_access_and_preview_all_five_report_types(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'name' => 'Budi Santoso', 'is_active' => true]);

        // A. Presensi
        $resAttendance = $this->actingAs($admin)->get('/admin/laporan?report_type=attendance&start_date=2026-11-01&end_date=2026-11-30');
        $resAttendance->assertOk();
        $resAttendance->assertSee('Rekapitulasi Presensi');

        // B. Izin & Sakit
        $resLeave = $this->actingAs($admin)->get('/admin/laporan?report_type=leave&start_date=2026-11-01&end_date=2026-11-30');
        $resLeave->assertOk();
        $resLeave->assertSee('Rekapitulasi Pengajuan Izin');

        // C. Logbook Manual
        $resLogbook = $this->actingAs($admin)->get('/admin/laporan?report_type=logbook&start_date=2026-11-01&end_date=2026-11-30');
        $resLogbook->assertOk();
        $resLogbook->assertSee('Rekapitulasi Logbook');

        // D. Tugas
        $resTask = $this->actingAs($admin)->get('/admin/laporan?report_type=task&start_date=2026-11-01&end_date=2026-11-30');
        $resTask->assertOk();
        $resTask->assertSee('Rekapitulasi Penugasan');

        // E. Monitoring Komposit
        $resMonitoring = $this->actingAs($admin)->get('/admin/laporan?report_type=monitoring&start_date=2026-11-01&end_date=2026-11-30');
        $resMonitoring->assertOk();
        $resMonitoring->assertSee('Rekapitulasi Monitoring Evaluasi Peserta Magang');
        $resMonitoring->assertSee('Budi Santoso');
    }

    /**
     * 3. Admin dapat mengunduh ekspor PDF (application/pdf) dan tercatat di audit_logs.
     */
    public function test_admin_can_export_pdf_and_logs_audit(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $response = $this->actingAs($admin)->get('/admin/laporan/export-pdf?report_type=attendance&start_date=2026-11-01&end_date=2026-11-30');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'report_exported',
            'entity_type' => 'report',
            'actor_user_id' => $admin->id,
        ]);
    }

    /**
     * 4. Admin dapat mengunduh ekspor Excel (.xlsx) dan tercatat di audit_logs.
     */
    public function test_admin_can_export_excel_and_logs_audit(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $response = $this->actingAs($admin)->get('/admin/laporan/export-excel?report_type=monitoring&start_date=2026-11-01&end_date=2026-11-30');

        $response->assertOk();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'report_exported',
            'entity_type' => 'report',
            'actor_user_id' => $admin->id,
        ]);
    }

    /**
     * 5. Validasi backend menolak parameter laporan invalid dan end_date < start_date.
     */
    public function test_export_validation_rejects_invalid_inputs(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        // Jenis laporan invalid
        $resInvalidType = $this->actingAs($admin)->get('/admin/laporan/export-pdf?report_type=arbitrary&start_date=2026-11-01&end_date=2026-11-30');
        $resInvalidType->assertSessionHasErrors(['report_type']);

        // end_date lebih awal dari start_date
        $resInvalidDates = $this->actingAs($admin)->get('/admin/laporan/export-pdf?report_type=attendance&start_date=2026-11-30&end_date=2026-11-01');
        $resInvalidDates->assertSessionHasErrors(['end_date']);
    }

    /**
     * 6. Konsistensi Presensi: Presensi dengan verification_status = 'rejected'
     * tidak dihitung sebagai hadir sah (Attendance::scopeValid()).
     */
    public function test_rejected_attendance_is_not_counted_as_valid_presence(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'name' => 'Peserta Uji', 'is_active' => true]);
        $media = $this->createMedia($intern);

        // Presensi sah
        Attendance::create([
            'intern_user_id' => $intern->id,
            'attendance_date' => '2026-11-10',
            'checked_in_at' => '2026-11-10 07:55:00',
            'status' => 'on_time',
            'verification_status' => 'verified',
            'photo_media_id' => $media->id,
        ]);

        // Presensi ditolak HR
        Attendance::create([
            'intern_user_id' => $intern->id,
            'attendance_date' => '2026-11-11',
            'checked_in_at' => '2026-11-11 08:30:00',
            'status' => 'late',
            'verification_status' => 'rejected',
            'photo_media_id' => $media->id,
            'notes' => 'Foto blur, bukan di lokasi',
        ]);

        $response = $this->actingAs($admin)->get('/admin/laporan?report_type=attendance&start_date=2026-11-01&end_date=2026-11-30');
        $response->assertOk();

        // Di preview, Hadir Sah harus 1, Ditolak HR harus 1
        $reportData = $response->viewData('reportData');
        $this->assertEquals(1, $reportData['summary']['total_valid_presence']);
        $this->assertEquals(1, $reportData['summary']['rejected_count']);
    }

    /**
     * 7. Konsistensi Logbook: Catatan logbook dengan source = 'task_assignment'
     * tidak masuk ke dalam rekapitulasi logbook manual.
     */
    public function test_task_assignment_logbook_is_excluded_from_manual_logbook_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // Logbook manual
        Logbook::create([
            'intern_user_id' => $intern->id,
            'log_date' => '2026-11-10',
            'title' => 'Eksperimen Manual UI',
            'activity_description' => 'Mengerjakan mockup dan styling halaman laporan.',
            'result' => 'Selesai 100%',
            'source' => LogbookSource::Manual,
            'status' => LogbookStatus::Approved,
        ]);

        // Logbook otomatis dari penugasan tugas
        Logbook::create([
            'intern_user_id' => $intern->id,
            'log_date' => '2026-11-10',
            'title' => 'Penugasan Otomatis Sistem',
            'activity_description' => 'Logbook otomatis dibuat dari task assignment.',
            'result' => null,
            'source' => LogbookSource::TaskAssignment,
            'status' => LogbookStatus::Pending,
        ]);

        $response = $this->actingAs($admin)->get('/admin/laporan?report_type=logbook&start_date=2026-11-01&end_date=2026-11-30');
        $response->assertOk();
        $response->assertSee('Eksperimen Manual UI');
        $response->assertDontSee('Penugasan Otomatis Sistem');

        $reportData = $response->viewData('reportData');
        $this->assertEquals(1, $reportData['summary']['total_entries']);
    }

    /**
     * 8. Batasan Bisnis: Laporan tidak memuat formula nilai akhir, skor, grade, atau bobot otomatis apa pun.
     */
    public function test_reports_do_not_contain_final_score_or_grading_formulas(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'name' => 'Peserta Monitoring', 'is_active' => true]);

        $response = $this->actingAs($admin)->get('/admin/laporan?report_type=monitoring&start_date=2026-11-01&end_date=2026-11-30');
        $response->assertOk();

        $content = strtolower($response->getContent());
        $this->assertStringNotContainsString('nilai akhir', $content);
        $this->assertStringNotContainsString('grade', $content);
        $this->assertStringNotContainsString('skor akhir', $content);
    }

    /**
     * 9. Filter peserta spesifik (intern_id) menyaring data hanya milik peserta tersebut.
     */
    public function test_filter_by_intern_id_shows_only_specific_intern_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $internA = User::factory()->create(['role' => 'intern', 'name' => 'Peserta A', 'is_active' => true]);
        $internB = User::factory()->create(['role' => 'intern', 'name' => 'Peserta B', 'is_active' => true]);
        $mediaA = $this->createMedia($internA);
        $mediaB = $this->createMedia($internB);

        Attendance::create([
            'intern_user_id' => $internA->id,
            'attendance_date' => '2026-11-12',
            'checked_in_at' => '2026-11-12 07:50:00',
            'status' => 'on_time',
            'verification_status' => 'verified',
            'photo_media_id' => $mediaA->id,
        ]);

        Attendance::create([
            'intern_user_id' => $internB->id,
            'attendance_date' => '2026-11-12',
            'checked_in_at' => '2026-11-12 07:55:00',
            'status' => 'on_time',
            'verification_status' => 'verified',
            'photo_media_id' => $mediaB->id,
        ]);

        $response = $this->actingAs($admin)->get("/admin/laporan?report_type=attendance&start_date=2026-11-01&end_date=2026-11-30&intern_id={$internA->id}");
        $response->assertOk();

        $reportData = $response->viewData('reportData');
        $this->assertEquals(1, $reportData['summary']['total_records']);
        $this->assertEquals($internA->id, $reportData['attendances']->first()->intern_user_id);
        $this->assertCount(1, $reportData['attendances']);
        $this->assertEquals('Peserta A', $reportData['attendances']->first()->intern->name);
    }

    /**
     * 10. Laporan tugas memperkaya informasi dengan riwayat pengumpulan (TaskSubmission) dan review PIC (TaskReview).
     */
    public function test_task_report_includes_submission_count_and_latest_review_details(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'name' => 'Peserta Tugas', 'is_active' => true]);

        $task = Task::create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'title' => 'Implementasi Modul Ekspor',
            'description' => 'Membuat fitur ekspor PDF dan Excel.',
            'assigned_at' => '2026-11-10 09:00:00',
            'deadline_at' => '2026-11-20 17:00:00',
            'status' => TaskStatus::RevisionRequested,
        ]);

        $submission = \App\Models\TaskSubmission::create([
            'task_id' => $task->id,
            'submitted_by' => $intern->id,
            'submission_number' => 1,
            'submission_note' => 'Sudah selesai tahap awal.',
            'submitted_at' => '2026-11-15 14:00:00',
        ]);

        \App\Models\TaskReview::create([
            'task_submission_id' => $submission->id,
            'reviewed_by' => $pic->id,
            'decision' => \App\Enums\TaskReviewDecision::RevisionRequested,
            'feedback' => 'Tolong rapikan format tabel PDF.',
            'reviewed_at' => '2026-11-16 10:00:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/laporan?report_type=task&start_date=2026-11-01&end_date=2026-11-30');
        $response->assertOk();

        // Cek data di view
        $reportData = $response->viewData('reportData');
        $retrievedTask = $reportData['tasks']->first();
        $this->assertEquals(1, $retrievedTask->submissions_count);
        $this->assertNotNull($retrievedTask->latestSubmission);
        $this->assertEquals(1, $retrievedTask->latestSubmission->submission_number);
        $this->assertNotNull($retrievedTask->latestSubmission->latestReview);
        $this->assertEquals(\App\Enums\TaskReviewDecision::RevisionRequested, $retrievedTask->latestSubmission->latestReview->decision);

        // Cek teks pada HTML preview
        $response->assertSee('Versi ke-1 (Total 1x)');
        $response->assertSee('Perlu Revisi');

        // Cek teks pada ekspor PDF & Excel
        $pdfResponse = $this->actingAs($admin)->get('/admin/laporan/export-pdf?report_type=task&start_date=2026-11-01&end_date=2026-11-30');
        $pdfResponse->assertOk();

        $excelResponse = $this->actingAs($admin)->get('/admin/laporan/export-excel?report_type=task&start_date=2026-11-01&end_date=2026-11-30');
        $excelResponse->assertOk();
    }
}
