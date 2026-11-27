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
use App\Models\PicAssignment;
use App\Models\Task;
use App\Models\TaskReview;
use App\Models\TaskSubmission;
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
     * 2. Admin dapat mengakses halaman laporan dan melihat preview ke-4 jenis laporan resmi:
     * - attendance (Rekap Presensi)
     * - leave (Rekap Izin & Sakit)
     * - logbook (Rekap Logbook & Tugas)
     * - monitoring (Rekap Monitoring Peserta)
     */
    public function test_admin_can_access_and_preview_all_four_report_types(): void
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

        // C. Logbook & Tugas Gabungan
        $resLogbook = $this->actingAs($admin)->get('/admin/laporan?report_type=logbook&start_date=2026-11-01&end_date=2026-11-30');
        $resLogbook->assertOk();
        $resLogbook->assertSee('Rekapitulasi Logbook &amp; Tugas', false);

        // D. Monitoring Komposit
        $resMonitoring = $this->actingAs($admin)->get('/admin/laporan?report_type=monitoring&start_date=2026-11-01&end_date=2026-11-30');
        $resMonitoring->assertOk();
        $resMonitoring->assertSee('Rekapitulasi Monitoring Evaluasi Peserta Magang');
        $resMonitoring->assertSee('Budi Santoso');
    }

    /**
     * 3. Admin dapat mengunduh ekspor PDF (application/pdf) untuk SEMUA 4 jenis laporan dan tercatat di audit_logs.
     */
    public function test_admin_can_export_pdf_for_all_four_report_types_and_logs_audit(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $types = ['attendance', 'leave', 'logbook', 'monitoring'];

        foreach ($types as $type) {
            $response = $this->actingAs($admin)->get("/admin/laporan/export-pdf?report_type={$type}&start_date=2026-11-01&end_date=2026-11-30");
            $response->assertOk();
            $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));

            $disposition = $response->headers->get('content-disposition') ?? '';
            $this->assertStringContainsString("laporan_{$type}_", $disposition);
        }

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'report_exported',
            'entity_type' => 'report',
            'actor_user_id' => $admin->id,
        ]);
    }

    /**
     * 4. Admin dapat mengunduh ekspor Excel (.xlsx) untuk SEMUA 4 jenis laporan dan tercatat di audit_logs.
     */
    public function test_admin_can_export_excel_for_all_four_report_types_and_logs_audit(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $types = ['attendance', 'leave', 'logbook', 'monitoring'];

        foreach ($types as $type) {
            $response = $this->actingAs($admin)->get("/admin/laporan/export-excel?report_type={$type}&start_date=2026-11-01&end_date=2026-11-30");
            $response->assertOk();
            $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));

            $disposition = $response->headers->get('content-disposition') ?? '';
            $this->assertStringContainsString("laporan_{$type}_", $disposition);
        }

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'report_exported',
            'entity_type' => 'report',
            'actor_user_id' => $admin->id,
        ]);
    }

    /**
     * 5. Validasi backend menolak parameter laporan invalid dan end_date < start_date.
     * Opsi lama 'task' terpisah ditolak karena sudah digabung ke 'logbook'.
     */
    public function test_export_validation_rejects_invalid_inputs(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        // Jenis laporan invalid
        $resInvalidType = $this->actingAs($admin)->get('/admin/laporan/export-pdf?report_type=arbitrary&start_date=2026-11-01&end_date=2026-11-30');
        $resInvalidType->assertSessionHasErrors(['report_type']);

        // Opsi 'task' mandiri sudah tidak diizinkan
        $resTaskStandalone = $this->actingAs($admin)->get('/admin/laporan/export-pdf?report_type=task&start_date=2026-11-01&end_date=2026-11-30');
        $resTaskStandalone->assertSessionHasErrors(['report_type']);

        // end_date lebih awal dari start_date
        $resInvalidDates = $this->actingAs($admin)->get('/admin/laporan/export-pdf?report_type=attendance&start_date=2026-11-30&end_date=2026-11-01');
        $resInvalidDates->assertSessionHasErrors(['end_date']);
    }

    /**
     * 6. Revisi 1: Presensi ditolak tidak masuk ke laporan presensi sah;
     * Hari kerja tanpa presensi sah/izin tampil sebagai "Tidak Hadir";
     * Tanggal di masa depan (> today) tidak dianggap "Tidak Hadir".
     */
    public function test_attendance_report_excludes_rejected_and_shows_absent_for_working_days(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-18 12:00:00', 'Asia/Jakarta'));

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'name' => 'Peserta Uji Presensi', 'is_active' => true]);
        $media = $this->createMedia($intern);

        // 1) Presensi sah on_time pada hari kerja (10 Nov 2026 - Selasa -> 'Hadir')
        Attendance::create([
            'intern_user_id' => $intern->id,
            'attendance_date' => '2026-11-10',
            'checked_in_at' => '2026-11-10 07:55:00',
            'status' => 'on_time',
            'verification_status' => 'verified',
            'photo_media_id' => $media->id,
        ]);

        // 2) Presensi ditolak HR pada hari kerja (11 Nov 2026 - Rabu -> 'Tidak Hadir')
        Attendance::create([
            'intern_user_id' => $intern->id,
            'attendance_date' => '2026-11-11',
            'checked_in_at' => '2026-11-11 08:30:00',
            'status' => 'late',
            'verification_status' => 'rejected',
            'photo_media_id' => $media->id,
            'notes' => 'Foto selfie tidak sesuai lokasi',
        ]);

        // 3) Izin disetujui pada hari kerja (12 Nov 2026 - Kamis -> 'Izin')
        LeaveRequest::create([
            'intern_user_id' => $intern->id,
            'leave_type' => 'leave',
            'start_date' => '2026-11-12',
            'end_date' => '2026-11-12',
            'reason' => 'Urusan keluarga mendesak',
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        // 4) Presensi sah late pada hari kerja (13 Nov 2026 - Jumat -> 'Terlambat')
        Attendance::create([
            'intern_user_id' => $intern->id,
            'attendance_date' => '2026-11-13',
            'checked_in_at' => '2026-11-13 08:20:00',
            'status' => 'late',
            'verification_status' => 'verified',
            'photo_media_id' => $media->id,
        ]);

        // 5) Sakit disetujui pada hari kerja (16 Nov 2026 - Senin -> 'Sakit')
        LeaveRequest::create([
            'intern_user_id' => $intern->id,
            'leave_type' => 'sick',
            'start_date' => '2026-11-16',
            'end_date' => '2026-11-16',
            'reason' => 'Demam tinggi',
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        // Periode laporan: 10 Nov s.d. 20 Nov 2026 (hari ini 18 Nov, masa depan 19-20 Nov)
        $response = $this->actingAs($admin)->get("/admin/laporan?report_type=attendance&start_date=2026-11-10&end_date=2026-11-20&intern_id={$intern->id}");
        $response->assertOk();

        $reportData = $response->viewData('reportData');
        $attendances = $reportData['attendances'];

        // Row 10 Nov (Sah on_time -> 'Hadir')
        $row10 = $attendances->first(fn ($r) => $r['date']->format('Y-m-d') === '2026-11-10');
        $this->assertNotNull($row10);
        $this->assertEquals('Hadir', $row10['status']);

        // Row 11 Nov (Ditolak HR, tidak ada presensi sah lain -> tampil sebagai 'Tidak Hadir')
        $row11 = $attendances->first(fn ($r) => $r['date']->format('Y-m-d') === '2026-11-11');
        $this->assertNotNull($row11);
        $this->assertEquals('Tidak Hadir', $row11['status']);

        // Row 12 Nov ('Izin')
        $row12 = $attendances->first(fn ($r) => $r['date']->format('Y-m-d') === '2026-11-12');
        $this->assertNotNull($row12);
        $this->assertEquals('Izin', $row12['status']);

        // Row 13 Nov ('Terlambat')
        $row13 = $attendances->first(fn ($r) => $r['date']->format('Y-m-d') === '2026-11-13');
        $this->assertNotNull($row13);
        $this->assertEquals('Terlambat', $row13['status']);

        // Row 16 Nov ('Sakit')
        $row16 = $attendances->first(fn ($r) => $r['date']->format('Y-m-d') === '2026-11-16');
        $this->assertNotNull($row16);
        $this->assertEquals('Sakit', $row16['status']);

        // Row 19 Nov & 20 Nov (Masa depan > 18 Nov) TIDAK boleh masuk sebagai 'Tidak Hadir'
        $row19 = $attendances->first(fn ($r) => $r['date']->format('Y-m-d') === '2026-11-19');
        $this->assertNull($row19);

        // Ringkasan presensi: Hadir Valid = 2 (1 on_time + 1 late), Ditolak HR tidak ada di summary
        $this->assertEquals(2, $reportData['summary']['total_valid_presence']);
        $this->assertEquals(1, $reportData['summary']['on_time_count']);
        $this->assertEquals(1, $reportData['summary']['late_count']);
        $this->assertArrayNotHasKey('rejected_count', $reportData['summary']);

        // View menampilkan tepat 5 label standar tanpa Verifikasi HR dan tanpa kata "Sah"
        $response->assertSee('Hadir');
        $response->assertSee('Terlambat');
        $response->assertSee('Izin');
        $response->assertSee('Sakit');
        $response->assertSee('Tidak Hadir');
        $response->assertDontSee('Verifikasi HR');
        $response->assertDontSee('Izin Sah');
        $response->assertDontSee('Sakit Sah');
        $response->assertDontSee('Hadir Sah');
    }

    /**
     * 7. Revisi 2: Penggabungan Rekap Logbook & Tugas:
     * - Menampilkan logbook manual dan tugas PIC secara terpadu.
     * - Kolom Jenis ('Manual' / 'Tugas PIC') & Penanggung Jawab dari nama PIC.
     * - Logbook source 'task_assignment' tidak masuk ganda.
     */
    public function test_combined_logbook_and_task_report_contains_both_sources_with_pic_in_charge(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $picMentor = User::factory()->create(['role' => 'pic', 'name' => 'Pak Rudi PIC', 'is_active' => true]);
        $picTaskCreator = User::factory()->create(['role' => 'pic', 'name' => 'Bu Siska Tech Lead', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'name' => 'Peserta Aktif Gabungan', 'is_active' => true]);

        PicAssignment::create([
            'intern_user_id' => $intern->id,
            'pic_user_id' => $picMentor->id,
            'start_date' => '2026-11-01',
            'end_date' => null,
            'assigned_by' => $admin->id,
        ]);

        // A. Logbook manual
        Logbook::create([
            'intern_user_id' => $intern->id,
            'log_date' => '2026-11-10',
            'title' => 'Eksperimen Manual UI',
            'activity_description' => 'Mengerjakan mockup dan styling halaman laporan.',
            'source' => LogbookSource::Manual,
            'status' => LogbookStatus::Pending,
        ]);

        // B. Logbook otomatis (task_assignment) -> HARUS DIKECUALIKAN AGAR TIDAK GANDA
        Logbook::create([
            'intern_user_id' => $intern->id,
            'log_date' => '2026-11-11',
            'title' => 'Logbook Otomatis Task Assignment',
            'activity_description' => 'Otomatis dibuat sistem.',
            'source' => LogbookSource::TaskAssignment,
            'status' => LogbookStatus::Pending,
        ]);

        // C. Tugas dari PIC
        $task = Task::create([
            'pic_user_id' => $picTaskCreator->id,
            'intern_user_id' => $intern->id,
            'title' => 'Implementasi Modul Ekspor PDF',
            'description' => 'Fitur PDF portrait tanpa logo.',
            'assigned_at' => '2026-11-12 09:00:00',
            'deadline_at' => '2026-11-20 17:00:00',
            'status' => TaskStatus::InProgress,
        ]);

        $response = $this->actingAs($admin)->get('/admin/laporan?report_type=logbook&start_date=2026-11-01&end_date=2026-11-30');
        $response->assertOk();

        // Cek data gabungan
        $reportData = $response->viewData('reportData');
        $entries = $reportData['entries'];

        $this->assertEquals(2, $entries->count()); // 1 manual + 1 task, logbook task_assignment dikecualikan

        $manualEntry = $entries->firstWhere('source_type', 'manual');
        $taskEntry = $entries->firstWhere('source_type', 'task');

        $this->assertNotNull($manualEntry);
        $this->assertNotNull($taskEntry);

        $this->assertEquals('Manual', $manualEntry['type_label']);
        $this->assertEquals('Pak Rudi PIC', $manualEntry['pic_in_charge']); // Dari pembimbing aktif
        $this->assertEquals('Tugas PIC', $taskEntry['type_label']);
        $this->assertEquals('Bu Siska Tech Lead', $taskEntry['pic_in_charge']); // Dari PIC pembuat tugas

        // Assert di HTML preview
        $response->assertSee('Eksperimen Manual UI');
        $response->assertSee('Implementasi Modul Ekspor PDF');
        $response->assertDontSee('Logbook Otomatis Task Assignment');
        $response->assertSee('Pak Rudi PIC');
        $response->assertSee('Bu Siska Tech Lead');
    }

    /**
     * 8. Revisi 3: Rekap Monitoring Peserta:
     * - Hanya menampilkan Hadir Sah, Izin Sah, Sakit Sah.
     * - Hapus kolom Ditolak.
     * - Logbook Manual hanya jumlah angka saja.
     * - Bebas dari formula nilai akhir / skor.
     */
    public function test_monitoring_report_contains_only_valid_presence_and_manual_logbook_count(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'name' => 'Peserta Monitoring Refactor', 'is_active' => true]);
        $media = $this->createMedia($intern);

        // 1 Hadir Sah
        Attendance::create([
            'intern_user_id' => $intern->id,
            'attendance_date' => '2026-11-10',
            'checked_in_at' => '2026-11-10 07:50:00',
            'status' => 'on_time',
            'verification_status' => 'verified',
            'photo_media_id' => $media->id,
        ]);

        // 1 Presensi Ditolak (TIDAK BOLEH DIHITUNG/DITAMPILKAN DI MONITORING)
        Attendance::create([
            'intern_user_id' => $intern->id,
            'attendance_date' => '2026-11-11',
            'checked_in_at' => '2026-11-11 08:30:00',
            'status' => 'late',
            'verification_status' => 'rejected',
            'photo_media_id' => $media->id,
        ]);

        // 1 Izin Sah
        LeaveRequest::create([
            'intern_user_id' => $intern->id,
            'leave_type' => 'leave',
            'start_date' => '2026-11-12',
            'end_date' => '2026-11-12',
            'reason' => 'Izin keperluan kampus',
            'status' => 'approved',
            'reviewed_by' => $admin->id,
        ]);

        // 2 Logbook Manual
        Logbook::create([
            'intern_user_id' => $intern->id,
            'log_date' => '2026-11-10',
            'title' => 'Logbook 1',
            'activity_description' => 'Aktivitas 1',
            'source' => LogbookSource::Manual,
            'status' => LogbookStatus::Approved,
        ]);
        Logbook::create([
            'intern_user_id' => $intern->id,
            'log_date' => '2026-11-12',
            'title' => 'Logbook 2',
            'activity_description' => 'Aktivitas 2',
            'source' => LogbookSource::Manual,
            'status' => LogbookStatus::Pending,
        ]);

        $response = $this->actingAs($admin)->get('/admin/laporan?report_type=monitoring&start_date=2026-11-01&end_date=2026-11-30');
        $response->assertOk();

        $reportData = $response->viewData('reportData');
        $summary = collect($reportData['intern_summaries'])->firstWhere('intern.id', $intern->id);

        $this->assertEquals(1, $summary['valid_presence_count']);
        $this->assertEquals(1, $summary['approved_leave_days']);
        $this->assertEquals(0, $summary['approved_sick_days']);
        $this->assertEquals(2, $summary['logbooks_total']); // Hanya jumlah angka logbook manual
        $this->assertArrayNotHasKey('rejected_count', $summary);

        // Tidak ada "Catatan Evaluasi HR", kolom "Status Akun", atau label "Hadir Sah"
        $response->assertDontSee('Catatan Evaluasi HR');
        $response->assertDontSee('Status Akun');
        $response->assertDontSee('Hadir Sah');

        // Verifikasi juga template PDF monitoring bebas dari blok/kolom tersebut
        $pdfHtml = view('admin.reports.pdf.monitoring', [
            'data' => $reportData,
            'metadata' => $reportData['metadata'],
            'summary' => $reportData['summary'],
        ])->render();
        $this->assertStringNotContainsString('Catatan Evaluasi HR', $pdfHtml);
        $this->assertStringNotContainsString('Status Akun', $pdfHtml);
        $this->assertStringNotContainsString('Hadir Sah', $pdfHtml);

        // Tanpa skor akhir
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
        $attendances = $reportData['attendances'];

        // Semua baris kehadiran yang dievaluasi hanya milik Intern A
        $this->assertTrue($attendances->every(fn ($row) => $row['intern_user_id'] === $internA->id));
        $this->assertEquals(1, $reportData['summary']['total_valid_presence']);
    }
}
