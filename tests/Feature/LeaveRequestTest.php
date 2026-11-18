<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\LeaveRequest;
use App\Models\MediaFile;
use App\Models\User;
use App\Services\AttendanceRecapService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LeaveRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local_private');
    }

    public function test_intern_can_submit_leave_request_and_status_is_forced_pending(): void
    {
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $file = UploadedFile::fake()->create('surat_dokter.pdf', 500, 'application/pdf');

        // Mencoba menyusupkan status 'approved' dalam request
        $response = $this->actingAs($intern)->post('/intern/izin', [
            'leave_type' => 'sick',
            'start_date' => '2026-11-20',
            'end_date' => '2026-11-21',
            'reason' => 'Sakit demam butuh istirahat dokter',
            'evidence' => $file,
            'status' => 'approved',
        ]);

        $response->assertRedirect('/intern/izin');
        $response->assertSessionHas('success');

        // Status WAJIB tetap 'pending'
        $leave = LeaveRequest::where('intern_user_id', $intern->id)->first();
        $this->assertNotNull($leave);
        $this->assertEquals('2026-11-20', $leave->start_date->format('Y-m-d'));
        $this->assertEquals('2026-11-21', $leave->end_date->format('Y-m-d'));
        $this->assertEquals('pending', $leave->status);
        $this->assertNull($leave->reviewed_by);
        $this->assertNull($leave->reviewed_at);

        $this->assertNotNull($leave->evidence_media_id);

        $media = MediaFile::find($leave->evidence_media_id);
        $this->assertNotNull($media);
        $this->assertEquals('leave_evidence', $media->category);
        Storage::disk('local_private')->assertExists($media->file_path);
    }

    public function test_validation_rejects_invalid_date_range_overlap_and_disallowed_files(): void
    {
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // 1. end_date < start_date ditolak
        $response1 = $this->actingAs($intern)->post('/intern/izin', [
            'leave_type' => 'leave',
            'start_date' => '2026-11-25',
            'end_date' => '2026-11-20',
            'reason' => 'Keperluan keluarga penting',
            'evidence' => UploadedFile::fake()->create('doc.pdf', 100),
        ]);
        $response1->assertSessionHasErrors('end_date');

        // 2. MIME terlarang (misal .exe atau .txt) ditolak
        $response2 = $this->actingAs($intern)->post('/intern/izin', [
            'leave_type' => 'leave',
            'start_date' => '2026-11-20',
            'end_date' => '2026-11-21',
            'reason' => 'Keperluan keluarga penting',
            'evidence' => UploadedFile::fake()->create('script.sh', 100, 'text/x-shellscript'),
        ]);
        $response2->assertSessionHasErrors('evidence');

        // 3. Ukuran melebihi 5 MB (> 5120 KB) ditolak
        $response3 = $this->actingAs($intern)->post('/intern/izin', [
            'leave_type' => 'leave',
            'start_date' => '2026-11-20',
            'end_date' => '2026-11-21',
            'reason' => 'Keperluan keluarga penting',
            'evidence' => UploadedFile::fake()->create('huge.pdf', 6000, 'application/pdf'),
        ]);
        $response3->assertSessionHasErrors('evidence');

        // 4. Overlapping date dengan izin pending sebelumnya ditolak
        // Buat pengajuan pertama (2026-11-23 s/d 2026-11-25)
        $this->actingAs($intern)->post('/intern/izin', [
            'leave_type' => 'leave',
            'start_date' => '2026-11-23',
            'end_date' => '2026-11-25',
            'reason' => 'Pengajuan pertama valid',
            'evidence' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
        ])->assertSessionHas('success');

        // Pengajuan kedua menabrak tanggal 2026-11-24 s/d 2026-11-26
        $response4 = $this->actingAs($intern)->post('/intern/izin', [
            'leave_type' => 'leave',
            'start_date' => '2026-11-24',
            'end_date' => '2026-11-26',
            'reason' => 'Pengajuan kedua yang tumpang tindih',
            'evidence' => UploadedFile::fake()->create('doc2.pdf', 100, 'application/pdf'),
        ]);
        $response4->assertSessionHas('error');
    }

    public function test_admin_can_approve_and_reject_with_required_notes_and_audit_trail(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $media = MediaFile::create([
            'disk' => 'local_private',
            'file_path' => 'evidence/surat1.pdf',
            'file_name' => 'surat1.pdf',
            'original_name' => 'surat1.pdf',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 1024,
            'category' => 'leave_evidence',
            'owner_user_id' => $intern->id,
        ]);

        $leave1 = LeaveRequest::create([
            'intern_user_id' => $intern->id,
            'leave_type' => 'sick',
            'start_date' => '2026-11-20',
            'end_date' => '2026-11-20',
            'reason' => 'Sakit demam flu',
            'evidence_media_id' => $media->id,
            'status' => 'pending',
        ]);

        // A. Admin menyetujui pengajuan
        Carbon::setTestNow(Carbon::parse('2026-11-19 14:00:00', 'Asia/Jakarta'));

        $responseApprove = $this->actingAs($admin)->post("/admin/izin/{$leave1->id}/review", [
            'decision' => 'approved',
            'review_notes' => 'Surat dokter valid terkonfirmasi.',
        ]);

        $responseApprove->assertSessionHas('success');

        $leave1->refresh();
        $this->assertEquals('approved', $leave1->status);
        $this->assertEquals($admin->id, $leave1->reviewed_by);
        $this->assertNotNull($leave1->reviewed_at);

        $this->assertDatabaseHas('audit_logs', [
            'actor_user_id' => $admin->id,
            'action' => 'leave_request_approved',
            'entity_type' => 'leave_requests',
            'entity_id' => $leave1->id,
        ]);

        // B. Admin menolak tanpa review_notes => ditolak validasi
        $leave2 = LeaveRequest::create([
            'intern_user_id' => $intern->id,
            'leave_type' => 'leave',
            'start_date' => '2026-11-26',
            'end_date' => '2026-11-26',
            'reason' => 'Izin keperluan keluarga',
            'evidence_media_id' => $media->id,
            'status' => 'pending',
        ]);

        $responseRejectFail = $this->actingAs($admin)->post("/admin/izin/{$leave2->id}/review", [
            'decision' => 'rejected',
            'review_notes' => '',
        ]);
        $responseRejectFail->assertSessionHasErrors('review_notes');

        // C. Admin menolak dengan review_notes => sukses
        $responseRejectSuccess = $this->actingAs($admin)->post("/admin/izin/{$leave2->id}/review", [
            'decision' => 'rejected',
            'review_notes' => 'Dokumen lampiran bukan surat resmi, harap perbaiki.',
        ]);
        $responseRejectSuccess->assertSessionHas('success');

        $leave2->refresh();
        $this->assertEquals('rejected', $leave2->status);
    }

    public function test_cannot_re_review_already_processed_leave_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $media = MediaFile::create([
            'disk' => 'local_private',
            'file_path' => 'evidence/surat.pdf',
            'file_name' => 'surat.pdf',
            'original_name' => 'surat.pdf',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 1024,
            'category' => 'leave_evidence',
            'owner_user_id' => $intern->id,
        ]);

        $leave = LeaveRequest::create([
            'intern_user_id' => $intern->id,
            'leave_type' => 'sick',
            'start_date' => '2026-11-20',
            'end_date' => '2026-11-20',
            'reason' => 'Sakit flu',
            'evidence_media_id' => $media->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        // Coba review ulang pengajuan yang sudah approved
        $response = $this->actingAs($admin)->post("/admin/izin/{$leave->id}/review", [
            'decision' => 'rejected',
            'review_notes' => 'Mencoba ubah keputusan',
        ]);

        $response->assertSessionHas('error');
        $leave->refresh();
        $this->assertEquals('approved', $leave->status);
    }

    public function test_pic_and_intern_cannot_review_leave_requests(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $media = MediaFile::create([
            'disk' => 'local_private',
            'file_path' => 'evidence/surat.pdf',
            'file_name' => 'surat.pdf',
            'original_name' => 'surat.pdf',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 1024,
            'category' => 'leave_evidence',
            'owner_user_id' => $intern->id,
        ]);

        $leave = LeaveRequest::create([
            'intern_user_id' => $intern->id,
            'leave_type' => 'sick',
            'start_date' => '2026-11-20',
            'end_date' => '2026-11-20',
            'reason' => 'Sakit',
            'evidence_media_id' => $media->id,
            'status' => 'pending',
        ]);

        // PIC hit endpoint review => 403 Forbidden
        $this->actingAs($pic)->post("/admin/izin/{$leave->id}/review", [
            'decision' => 'approved',
        ])->assertStatus(403);

        // Intern hit endpoint review => 403 Forbidden
        $this->actingAs($intern)->post("/admin/izin/{$leave->id}/review", [
            'decision' => 'approved',
        ])->assertStatus(403);
    }

    public function test_attendance_recap_service_only_excuses_approved_leaves_for_ews(): void
    {
        $recapService = app(AttendanceRecapService::class);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $media = MediaFile::create([
            'disk' => 'local_private',
            'file_path' => 'evidence/surat.pdf',
            'file_name' => 'surat.pdf',
            'original_name' => 'surat.pdf',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 1024,
            'category' => 'leave_evidence',
            'owner_user_id' => $intern->id,
        ]);

        // A. Status Pending pada tanggal 2026-11-24 => Belum sah, isInternExcusedOnDate() = false
        $pendingLeave = LeaveRequest::create([
            'intern_user_id' => $intern->id,
            'leave_type' => 'sick',
            'start_date' => '2026-11-24',
            'end_date' => '2026-11-24',
            'reason' => 'Sakit pending',
            'evidence_media_id' => $media->id,
            'status' => 'pending',
        ]);

        $this->assertFalse($recapService->isInternExcusedOnDate($intern->id, '2026-11-24'));

        // B. Status Rejected pada tanggal 2026-11-25 => Tidak sah, isInternExcusedOnDate() = false
        $rejectedLeave = LeaveRequest::create([
            'intern_user_id' => $intern->id,
            'leave_type' => 'leave',
            'start_date' => '2026-11-25',
            'end_date' => '2026-11-25',
            'reason' => 'Izin ditolak',
            'evidence_media_id' => $media->id,
            'status' => 'rejected',
        ]);

        $this->assertFalse($recapService->isInternExcusedOnDate($intern->id, '2026-11-25'));

        // C. Status Approved pada tanggal 2026-11-26 => SAH, isInternExcusedOnDate() = true (bebas Alpha EWS)
        $approvedLeave = LeaveRequest::create([
            'intern_user_id' => $intern->id,
            'leave_type' => 'leave',
            'start_date' => '2026-11-26',
            'end_date' => '2026-11-26',
            'reason' => 'Izin sah disetujui',
            'evidence_media_id' => $media->id,
            'status' => 'approved',
        ]);

        $this->assertTrue($recapService->isInternExcusedOnDate($intern->id, '2026-11-26'));
    }
}
