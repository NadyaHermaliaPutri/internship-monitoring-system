<?php

namespace Tests\Feature\Task;

use App\Enums\TaskStatus;
use App\Models\Logbook;
use App\Models\MediaFile;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TaskAttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 11, 26, 9, 0, 0, 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_pic_can_create_task_with_or_without_attachment_stored_on_local_private_disk(): void
    {
        Storage::fake('local_private');
        Storage::fake('public');

        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // 1. Membuat tugas DENGAN lampiran PDF
        $file = UploadedFile::fake()->create('Brief-Modul-3.pdf', 512, 'application/pdf');

        $responseWithFile = $this->actingAs($pic)->post(route('pic.tasks.store'), [
            'intern_user_id' => $intern->id,
            'title' => 'Implementasi Fitur Lampiran Tugas',
            'description' => 'Pelajari dokumen brief terlampir dan implementasikan sesuai spesifikasi.',
            'deadline_at' => '2026-11-29 17:00',
            'attachment_file' => $file,
        ]);

        $taskWithFile = Task::query()->where('title', 'Implementasi Fitur Lampiran Tugas')->firstOrFail();
        $responseWithFile->assertRedirect(route('pic.tasks.show', $taskWithFile));

        $this->assertNotNull($taskWithFile->attachment_media_id);

        $media = MediaFile::findOrFail($taskWithFile->attachment_media_id);
        $this->assertSame($pic->id, $media->owner_user_id);
        $this->assertSame('task_attachment', $media->category);
        $this->assertSame('local_private', $media->disk);
        $this->assertSame('Brief-Modul-3.pdf', $media->original_name);
        $this->assertStringStartsWith('tugas-lampiran/', $media->file_path);

        Storage::disk('local_private')->assertExists($media->file_path);
        Storage::disk('public')->assertMissing($media->file_path);
        $this->assertDatabaseHas('logbooks', ['task_id' => $taskWithFile->id]);

        // 2. Membuat tugas TANPA lampiran (opsional)
        $responseWithoutFile = $this->actingAs($pic)->post(route('pic.tasks.store'), [
            'intern_user_id' => $intern->id,
            'title' => 'Review Kode Harian',
            'description' => 'Lakukan review kode pada branch fitur dan catat temuan penting.',
            'deadline_at' => '2026-11-29 17:00',
        ]);

        $taskWithoutFile = Task::query()->where('title', 'Review Kode Harian')->firstOrFail();
        $responseWithoutFile->assertRedirect(route('pic.tasks.show', $taskWithoutFile));
        $this->assertNull($taskWithoutFile->attachment_media_id);
    }

    public function test_invalid_mime_type_or_oversized_attachment_is_rejected_with_422(): void
    {
        Storage::fake('local_private');

        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // 1. Format berbahaya / tidak diizinkan (.php / .exe)
        $this->actingAs($pic)
            ->postJson(route('pic.tasks.store'), [
                'intern_user_id' => $intern->id,
                'title' => 'Tugas Dengan File Terlarang',
                'description' => 'Mencoba mengunggah file executable atau script php.',
                'deadline_at' => '2026-11-29 17:00',
                'attachment_file' => UploadedFile::fake()->create('exploit.php', 50, 'application/x-httpd-php'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('attachment_file');

        // 2. Ukuran melebihi batas maksimal 2 MB (2048 KB), misal 2.5 MB (2560 KB)
        $this->actingAs($pic)
            ->postJson(route('pic.tasks.store'), [
                'intern_user_id' => $intern->id,
                'title' => 'Tugas Dengan File Terlalu Besar',
                'description' => 'Mencoba mengunggah lampiran melebihi batas ukuran 2 MB.',
                'deadline_at' => '2026-11-29 17:00',
                'attachment_file' => UploadedFile::fake()->create('oversized.pdf', 2560, 'application/pdf'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('attachment_file');

        $this->assertDatabaseCount('tasks', 0);
        $this->assertDatabaseCount('media_files', 0);
    }

    public function test_recipient_intern_owner_pic_and_admin_can_download_task_attachment_with_original_filename(): void
    {
        Storage::fake('local_private');

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $this->actingAs($pic)->post(route('pic.tasks.store'), [
            'intern_user_id' => $intern->id,
            'title' => 'Tugas Desain Wireframe',
            'description' => 'Ikuti panduan wireframe yang terdapat pada file PDF lampiran.',
            'deadline_at' => '2026-11-29 17:00',
            'attachment_file' => UploadedFile::fake()->create('Panduan-Wireframe.pdf', 256, 'application/pdf'),
        ]);

        $task = Task::query()->firstOrFail();
        $media = MediaFile::findOrFail($task->attachment_media_id);
        $downloadUrl = route('media.private.show', ['mediaFile' => $media->id, 'download' => 1]);

        // Halaman detail tugas peserta menampilkan informasi lampiran & tombol unduh
        $this->actingAs($intern)
            ->get(route('intern.tasks.show', $task))
            ->assertOk()
            ->assertSee('Lampiran Tugas dari PIC')
            ->assertSee('Panduan-Wireframe.pdf')
            ->assertSee('Unduh Lampiran');

        // Peserta penerima tugas, PIC pembuat, dan Admin semuanya mendapat 200 OK + Content-Disposition attachment
        foreach ([$intern, $pic, $admin] as $authorizedUser) {
            $downloadResponse = $this->actingAs($authorizedUser)->get($downloadUrl);
            $downloadResponse->assertOk();
            $downloadResponse->assertHeader('Content-Disposition', 'attachment; filename="Panduan-Wireframe.pdf"');
        }
    }

    public function test_other_intern_and_other_pic_are_forbidden_from_downloading_task_attachment(): void
    {
        Storage::fake('local_private');

        $picOwner = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $otherPic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $recipientIntern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $otherIntern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $this->actingAs($picOwner)->post(route('pic.tasks.store'), [
            'intern_user_id' => $recipientIntern->id,
            'title' => 'Tugas Rahasia Internal',
            'description' => 'Hanya boleh diakses oleh PIC pembuat, peserta penerima, dan Admin.',
            'deadline_at' => '2026-11-29 17:00',
            'attachment_file' => UploadedFile::fake()->create('Spesifikasi-Rahasia.pdf', 128, 'application/pdf'),
        ]);

        $task = Task::query()->firstOrFail();
        $media = MediaFile::findOrFail($task->attachment_media_id);
        $downloadUrl = route('media.private.show', ['mediaFile' => $media->id, 'download' => 1]);

        $this->actingAs($otherIntern)->get($downloadUrl)->assertForbidden();
        $this->actingAs($otherPic)->get($downloadUrl)->assertForbidden();
    }

    public function test_pic_can_update_task_attachment_before_accepted_without_duplicating_automatic_logbook(): void
    {
        Storage::fake('local_private');

        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $this->actingAs($pic)->post(route('pic.tasks.store'), [
            'intern_user_id' => $intern->id,
            'title' => 'Tugas Dokumentasi API',
            'description' => 'Susun dokumentasi API berdasarkan template versi awal.',
            'deadline_at' => '2026-11-29 17:00',
            'attachment_file' => UploadedFile::fake()->create('Template-v1.pdf', 200, 'application/pdf'),
        ]);

        $task = Task::query()->firstOrFail();
        $firstMediaId = $task->attachment_media_id;
        $this->assertSame(1, Logbook::query()->where('task_id', $task->id)->count());

        // PIC mengedit tugas dan mengganti lampiran ke versi 2
        $updateResponse = $this->actingAs($pic)->put(route('pic.tasks.update', $task), [
            'title' => 'Tugas Dokumentasi API (Revisi Brief)',
            'description' => 'Susun dokumentasi API menggunakan template versi terbaru.',
            'deadline_at' => '2026-11-30 17:00',
            'attachment_file' => UploadedFile::fake()->create('Template-v2.docx', 300, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ]);

        $updateResponse->assertRedirect(route('pic.tasks.show', $task));

        $task->refresh();
        $this->assertNotNull($task->attachment_media_id);
        $this->assertNotSame($firstMediaId, $task->attachment_media_id);

        $secondMedia = MediaFile::findOrFail($task->attachment_media_id);
        $this->assertSame('Template-v2.docx', $secondMedia->original_name);
        $this->assertSame('task_attachment', $secondMedia->category);
        Storage::disk('local_private')->assertExists($secondMedia->file_path);

        // Logbook otomatis TIDAK boleh terduplikasi
        $this->assertSame(1, Logbook::query()->where('task_id', $task->id)->count());

        // Tugas yang sudah accepted tidak bisa diperbarui lampirannya (403 Forbidden)
        $task->update(['status' => TaskStatus::Accepted, 'accepted_at' => now(), 'accepted_by' => $pic->id]);

        $this->actingAs($pic)->put(route('pic.tasks.update', $task), [
            'title' => 'Coba Ubah Setelah Accepted',
            'description' => 'Percobaan mengganti lampiran setelah tugas diterima.',
            'deadline_at' => '2026-12-01 17:00',
            'attachment_file' => UploadedFile::fake()->create('Template-v3.pdf', 150, 'application/pdf'),
        ])->assertForbidden();
    }
}
