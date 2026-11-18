<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\MediaFile;
use App\Models\PicAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrivateMediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local_private');
    }

    public function test_intern_a_gets_403_forbidden_when_accessing_intern_b_private_media(): void
    {
        $internA = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $internB = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // Simpan file di local_private untuk Intern B
        Storage::disk('local_private')->put('attendance/b_selfie.jpg', 'fake-image-content');

        $mediaB = MediaFile::create([
            'disk' => 'local_private',
            'file_path' => 'attendance/b_selfie.jpg',
            'file_name' => 'b_selfie.jpg',
            'original_name' => 'b_selfie.jpg',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => 1024,
            'category' => 'attendance_photo',
            'owner_user_id' => $internB->id,
        ]);

        Attendance::create([
            'intern_user_id' => $internB->id,
            'attendance_date' => '2026-11-18',
            'checked_in_at' => '2026-11-18 08:00:00',
            'status' => 'on_time',
            'photo_media_id' => $mediaB->id,
        ]);

        // Intern B dapat mengakses miliknya sendiri => 200 OK
        $this->actingAs($internB)
            ->get("/media/private/{$mediaB->id}")
            ->assertStatus(200);

        // Intern A mengakses milik Intern B => 403 Forbidden
        $this->actingAs($internA)
            ->get("/media/private/{$mediaB->id}")
            ->assertStatus(403);
    }

    public function test_pic_can_only_access_evidence_of_actively_assigned_intern(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $internAssigned = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $internNotAssigned = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // Penugasan aktif: end_date IS NULL
        PicAssignment::create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $internAssigned->id,
            'start_date' => '2026-11-01',
            'end_date' => null,
            'assigned_by' => $admin->id,
        ]);

        // File bukti untuk Intern Assigned
        Storage::disk('local_private')->put('leave/doc_assigned.pdf', 'pdf-assigned-content');
        $mediaAssigned = MediaFile::create([
            'disk' => 'local_private',
            'file_path' => 'leave/doc_assigned.pdf',
            'file_name' => 'doc_assigned.pdf',
            'original_name' => 'doc_assigned.pdf',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 2048,
            'category' => 'leave_evidence',
            'owner_user_id' => $internAssigned->id,
        ]);

        LeaveRequest::create([
            'intern_user_id' => $internAssigned->id,
            'leave_type' => 'sick',
            'start_date' => '2026-11-20',
            'end_date' => '2026-11-20',
            'reason' => 'Sakit',
            'evidence_media_id' => $mediaAssigned->id,
            'status' => 'pending',
        ]);

        // File bukti untuk Intern NOT Assigned
        Storage::disk('local_private')->put('leave/doc_other.pdf', 'pdf-other-content');
        $mediaOther = MediaFile::create([
            'disk' => 'local_private',
            'file_path' => 'leave/doc_other.pdf',
            'file_name' => 'doc_other.pdf',
            'original_name' => 'doc_other.pdf',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 2048,
            'category' => 'leave_evidence',
            'owner_user_id' => $internNotAssigned->id,
        ]);

        LeaveRequest::create([
            'intern_user_id' => $internNotAssigned->id,
            'leave_type' => 'sick',
            'start_date' => '2026-11-20',
            'end_date' => '2026-11-20',
            'reason' => 'Sakit peserta lain',
            'evidence_media_id' => $mediaOther->id,
            'status' => 'pending',
        ]);

        // 1. PIC mengakses bukti peserta bimbingan aktifnya => 200 OK
        $this->actingAs($pic)
            ->get("/media/private/{$mediaAssigned->id}")
            ->assertStatus(200);

        // 2. PIC mengakses bukti peserta yang BUKAN bimbingannya => 403 Forbidden
        $this->actingAs($pic)
            ->get("/media/private/{$mediaOther->id}")
            ->assertStatus(403);
    }

    public function test_admin_has_full_access_to_all_private_media(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        Storage::disk('local_private')->put('attendance/selfie.jpg', 'image-bytes');
        $media = MediaFile::create([
            'disk' => 'local_private',
            'file_path' => 'attendance/selfie.jpg',
            'file_name' => 'selfie.jpg',
            'original_name' => 'selfie.jpg',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => 1024,
            'category' => 'attendance_photo',
            'owner_user_id' => $intern->id,
        ]);

        $this->actingAs($admin)
            ->get("/media/private/{$media->id}")
            ->assertStatus(200);
    }

    public function test_inactive_user_is_blocked_from_accessing_module_2(): void
    {
        // Pengguna non-aktif (is_active = false)
        $inactiveIntern = User::factory()->create(['role' => 'intern', 'is_active' => false]);
        $inactiveAdmin = User::factory()->create(['role' => 'admin', 'is_active' => false]);

        $this->actingAs($inactiveIntern)
            ->get('/intern/presensi')
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->actingAs($inactiveIntern)
            ->get('/intern/izin')
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->actingAs($inactiveAdmin)
            ->get('/admin/kalender')
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->actingAs($inactiveAdmin)
            ->get('/admin/presensi')
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->actingAs($inactiveAdmin)
            ->get('/admin/izin')
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');
    }
}
