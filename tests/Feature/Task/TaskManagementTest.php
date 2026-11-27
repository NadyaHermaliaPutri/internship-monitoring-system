<?php

namespace Tests\Feature\Task;

use App\Enums\LogbookSource;
use App\Enums\TaskStatus;
use App\Models\Logbook;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 11, 18, 10, 0, 0, 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function assignInternToPic(User $pic, User $intern, string $startDate = '2026-11-01', ?string $endDate = null): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        DB::table('pic_assignments')->insert([
            'intern_user_id' => $intern->id,
            'pic_user_id' => $pic->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'assigned_by' => $admin->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_pic_can_create_task_for_active_assigned_intern_and_auto_creates_single_logbook(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $this->assignInternToPic($pic, $intern);

        $response = $this->actingAs($pic)->post(route('pic.tasks.store'), [
            'intern_user_id' => $intern->id,
            'title' => 'Implementasi Fitur Manajemen Tugas PIC',
            'description' => 'Buat migration, model, policy, dan controller untuk manajemen tugas.',
            'deadline_at' => '2026-11-21 17:00:00',
        ]);

        $task = Task::first();
        $this->assertNotNull($task);
        $response->assertRedirect(route('pic.tasks.show', $task));

        $this->assertSame(TaskStatus::Assigned, $task->status);
        $this->assertSame($pic->id, $task->pic_user_id);
        $this->assertSame($intern->id, $task->intern_user_id);

        // Pastikan tepat 1 baris logbook otomatis terbentuk
        $this->assertDatabaseCount('logbooks', 1);
        $this->assertDatabaseHas('logbooks', [
            'intern_user_id' => $intern->id,
            'task_id' => $task->id,
            'source' => LogbookSource::TaskAssignment->value,
            'status' => null,
            'log_date' => '2026-11-18',
        ]);

        // Pastikan tidak masuk antrian approval & tidak dihitung pemenuhan EWS manual
        $this->assertSame(0, Logbook::query()->pendingApproval()->count());
        $this->assertSame(0, Logbook::query()->forEwsFulfillment($intern->id, '2026-11-18')->count());
    }

    public function test_creating_task_validates_target_intern_role_active_status_and_future_deadline_and_supports_multi_pic(): void
    {
        $picA = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $picB = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $anotherPicAsTarget = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $inactiveIntern = User::factory()->create(['role' => 'intern', 'is_active' => false]);
        $activeIntern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // 1. Target bukan role intern (mis. memilih user ber-role pic) -> ditolak
        $this->actingAs($picA)->post(route('pic.tasks.store'), [
            'intern_user_id' => $anotherPicAsTarget->id,
            'title' => 'Tugas Uji Role',
            'description' => 'Deskripsi tugas uji validasi role peserta.',
            'deadline_at' => '2026-11-20 17:00:00',
        ])->assertSessionHasErrors('intern_user_id');

        // 2. Peserta nonaktif (is_active = false) -> ditolak
        $this->actingAs($picA)->post(route('pic.tasks.store'), [
            'intern_user_id' => $inactiveIntern->id,
            'title' => 'Tugas Uji Nonaktif',
            'description' => 'Deskripsi tugas uji validasi peserta nonaktif.',
            'deadline_at' => '2026-11-20 17:00:00',
        ])->assertSessionHasErrors('intern_user_id');

        // 3. Deadline di masa lalu (<= sekarang 10:00 WIB) -> ditolak
        $this->actingAs($picA)->post(route('pic.tasks.store'), [
            'intern_user_id' => $activeIntern->id,
            'title' => 'Tugas Uji Deadline Lewat',
            'description' => 'Deskripsi tugas uji deadline masa lalu.',
            'deadline_at' => '2026-11-18 09:30:00',
        ])->assertSessionHasErrors('deadline_at');

        $this->assertDatabaseCount('tasks', 0);

        // 4. Multi-PIC: PIC A dan PIC B sama-sama dapat membuat tugas untuk peserta aktif yang sama (tanpa harus ada di pic_assignments)
        $this->actingAs($picA)->post(route('pic.tasks.store'), [
            'intern_user_id' => $activeIntern->id,
            'title' => 'Tugas dari PIC A',
            'description' => 'Deskripsi tugas dari PIC A untuk peserta.',
            'deadline_at' => '2026-11-21 17:00:00',
        ])->assertSessionHasNoErrors();

        $this->actingAs($picB)->post(route('pic.tasks.store'), [
            'intern_user_id' => $activeIntern->id,
            'title' => 'Tugas dari PIC B',
            'description' => 'Deskripsi tugas dari PIC B untuk peserta yang sama.',
            'deadline_at' => '2026-11-22 17:00:00',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('tasks', 2);
        $this->assertDatabaseCount('logbooks', 2);
    }

    public function test_updating_task_or_extending_deadline_does_not_duplicate_auto_logbook_and_updates_overdue_status(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $this->assignInternToPic($pic, $intern);

        // Buat tugas baru melalui endpoint store
        $this->actingAs($pic)->post(route('pic.tasks.store'), [
            'intern_user_id' => $intern->id,
            'title' => 'Tugas Awal',
            'description' => 'Deskripsi tugas awal sebelum deadline lewat.',
            'deadline_at' => '2026-11-19 12:00:00',
        ]);

        /** @var Task $task */
        $task = Task::firstOrFail();
        $this->assertFalse($task->isOverdue());
        $this->assertDatabaseCount('logbooks', 1);

        // Majukan waktu melewati deadline awal (20 Nov 2026)
        Carbon::setTestNow(Carbon::create(2026, 11, 20, 9, 0, 0, 'Asia/Jakarta'));
        $this->assertTrue($task->fresh()->isOverdue());
        $this->assertSame(1, Task::query()->overdue()->count());

        // PIC memperpanjang deadline ke 25 Nov 2026
        $updateResponse = $this->actingAs($pic)->put(route('pic.tasks.update', $task), [
            'title' => 'Tugas Awal (Diperpanjang)',
            'description' => 'Deskripsi tugas setelah perpanjangan deadline oleh PIC.',
            'deadline_at' => '2026-11-25 17:00:00',
        ]);

        $updateResponse->assertRedirect(route('pic.tasks.show', $task));

        $task->refresh();
        $this->assertSame('Tugas Awal (Diperpanjang)', $task->title);
        $this->assertFalse($task->isOverdue());
        $this->assertSame(0, Task::query()->overdue()->count());

        // Pastikan logbook otomatis tetap 1 baris (tidak terduplikasi)
        $this->assertDatabaseCount('logbooks', 1);
    }

    public function test_accepted_task_is_not_overdue_and_cannot_be_edited_by_pic(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $acceptedTask = Task::factory()->accepted($pic)->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'deadline_at' => Carbon::now('Asia/Jakarta')->subDays(2), // Deadline sudah lewat
        ]);

        // Tugas yang sudah accepted tidak boleh dianggap overdue
        $this->assertFalse($acceptedTask->isOverdue());
        $this->assertSame(0, Task::query()->overdue()->count());

        // PIC tidak bisa mengedit tugas yang sudah accepted
        $this->actingAs($pic)
            ->get(route('pic.tasks.edit', $acceptedTask))
            ->assertForbidden();

        $this->actingAs($pic)
            ->put(route('pic.tasks.update', $acceptedTask), [
                'title' => 'Coba Edit Tugas Accepted',
                'description' => 'Percobaan mengedit tugas yang sudah diterima.',
                'deadline_at' => '2026-11-25 17:00:00',
            ])
            ->assertForbidden();
    }

    public function test_rbac_prevents_cross_pic_cross_intern_and_intern_creating_or_editing_tasks(): void
    {
        $picA = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $picB = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $internA = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $internB = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $taskA = Task::factory()->create([
            'pic_user_id' => $picA->id,
            'intern_user_id' => $internA->id,
        ]);

        // PIC B tidak boleh melihat atau mengedit tugas milik PIC A
        $this->actingAs($picB)
            ->get(route('pic.tasks.show', $taskA))
            ->assertForbidden();

        $this->actingAs($picB)
            ->put(route('pic.tasks.update', $taskA), [
                'title' => 'Diubah PIC Lain',
                'description' => 'Percobaan ubah tugas oleh PIC yang tidak berwenang.',
                'deadline_at' => '2026-11-25 17:00:00',
            ])
            ->assertForbidden();

        // Intern A bisa melihat tugas miliknya, tapi tidak bisa mengakses route pembuatan/edit PIC
        $this->actingAs($internA)
            ->get(route('intern.tasks.show', $taskA))
            ->assertOk();

        $this->actingAs($internA)
            ->post(route('pic.tasks.store'), [
                'intern_user_id' => $internA->id,
                'title' => 'Tugas Buat Sendiri',
                'description' => 'Percobaan peserta membuat tugas sendiri.',
                'deadline_at' => '2026-11-25 17:00:00',
            ])
            ->assertForbidden();

        // Intern B tidak boleh melihat tugas milik Intern A
        $this->actingAs($internB)
            ->get(route('intern.tasks.show', $taskA))
            ->assertForbidden();
    }

    public function test_intern_owner_can_transition_assigned_to_in_progress_and_cannot_self_accept_or_invalid_transition(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $internA = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $internB = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $task = Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $internA->id,
            'status' => TaskStatus::Assigned,
        ]);

        // Intern B (bukan pemilik) ditolak saat mencoba memulai tugas Intern A
        $this->actingAs($internB)
            ->patch(route('intern.tasks.start', $task))
            ->assertForbidden();

        // Intern A (pemilik) berhasil mengubah status assigned -> in_progress (meski mengirim payload status=accepted)
        $response = $this->actingAs($internA)
            ->patch(route('intern.tasks.start', $task), [
                'status' => TaskStatus::Accepted->value,
            ]);

        $response->assertRedirect(route('intern.tasks.show', $task));
        $this->assertSame(TaskStatus::InProgress, $task->fresh()->status);

        // Setelah status menjadi in_progress, panggilan ulang ke endpoint start ditolak (403)
        $this->actingAs($internA)
            ->patch(route('intern.tasks.start', $task))
            ->assertForbidden();
    }

    public function test_pic_can_create_and_update_task_with_optional_null_description_and_views_render_cleanly(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // 1. PIC membuat tugas baru TANPA description
        $createResponse = $this->actingAs($pic)->post(route('pic.tasks.store'), [
            'intern_user_id' => $intern->id,
            'title' => 'Tugas Tanpa Deskripsi Tambahan',
            'description' => '',
            'deadline_at' => '2026-11-25 17:00',
        ]);

        $task = Task::query()->where('title', 'Tugas Tanpa Deskripsi Tambahan')->firstOrFail();
        $createResponse->assertRedirect(route('pic.tasks.show', $task));
        $this->assertNull($task->description);
        $this->assertSame(TaskStatus::Assigned, $task->status);

        // Logbook otomatis tetap tercatat bersih tanpa label kosong "Deskripsi Tugas:"
        $autoLogbook = Logbook::query()->where('task_id', $task->id)->firstOrFail();
        $this->assertStringContainsString('memberikan tugas baru: "Tugas Tanpa Deskripsi Tambahan"', $autoLogbook->activity_description);
        $this->assertStringNotContainsString('Deskripsi Tugas:', $autoLogbook->activity_description);

        // 2. PIC mengedit tugas dan mengisi lalu mengosongkan kembali description
        $this->actingAs($pic)->put(route('pic.tasks.update', $task), [
            'title' => 'Tugas Tanpa Deskripsi Tambahan',
            'description' => 'Catatan sementara untuk tugas ini.',
            'deadline_at' => '2026-11-26 17:00',
        ])->assertRedirect(route('pic.tasks.show', $task));
        $this->assertSame('Catatan sementara untuk tugas ini.', $task->fresh()->description);

        $this->actingAs($pic)->put(route('pic.tasks.update', $task), [
            'title' => 'Tugas Tanpa Deskripsi Tambahan',
            'description' => '',
            'deadline_at' => '2026-11-26 17:00',
        ])->assertRedirect(route('pic.tasks.show', $task));
        $this->assertNull($task->fresh()->description);

        // 3. Halaman daftar & detail tugas (PIC dan Peserta) tampil normal 200 OK saat description = null
        $this->actingAs($pic)->get(route('pic.tasks.index'))->assertOk()->assertSee('Tugas Tanpa Deskripsi Tambahan');
        $this->actingAs($pic)->get(route('pic.tasks.show', $task))->assertOk()->assertSee('Tidak ada deskripsi tambahan.');
        $this->actingAs($intern)->get(route('intern.tasks.index'))->assertOk()->assertSee('Tugas Tanpa Deskripsi Tambahan');
        $this->actingAs($intern)->get(route('intern.tasks.show', $task))->assertOk()->assertSee('Tidak ada deskripsi tambahan.');
    }

    public function test_admin_sidebar_hides_task_menu_while_pic_and_intern_keep_it(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // Admin tidak melihat link menu Tugas (admin.tasks.index) di sidebar
        $this->actingAs($admin)
            ->get(route('admin.logbooks.index'))
            ->assertOk()
            ->assertDontSee('href="'.route('admin.tasks.index').'"', false);

        // PIC tetap melihat menu Tugas (pic.tasks.index) di sidebar
        $this->actingAs($pic)
            ->get(route('pic.logbooks.index'))
            ->assertOk()
            ->assertSee('href="'.route('pic.tasks.index').'"', false)
            ->assertSee('Tugas Peserta');

        // Peserta tetap melihat menu Tugas (intern.tasks.index) di sidebar
        $this->actingAs($intern)
            ->get(route('intern.logbooks.index'))
            ->assertOk()
            ->assertSee('href="'.route('intern.tasks.index').'"', false)
            ->assertSee('Tugas Peserta');
    }

    public function test_intern_task_kpi_cards_filter_accurately_and_respect_intern_scoping(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $otherIntern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // Tugas milik intern yang login untuk semua 6 kategori KPI:
        Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'title' => 'T1 Assigned Future',
            'status' => TaskStatus::Assigned,
            'deadline_at' => '2026-11-22 17:00:00',
        ]);
        Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'title' => 'T2 InProgress Overdue',
            'status' => TaskStatus::InProgress,
            'deadline_at' => '2026-11-17 17:00:00', // overdue (< 2026-11-18 10:00)
        ]);
        Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'title' => 'T3 Submitted',
            'status' => TaskStatus::Submitted,
            'deadline_at' => '2026-11-23 17:00:00',
        ]);
        Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'title' => 'T4 Resubmitted',
            'status' => TaskStatus::Resubmitted,
            'deadline_at' => '2026-11-24 17:00:00',
        ]);
        Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'title' => 'T5 Revision Overdue',
            'status' => TaskStatus::RevisionRequested,
            'deadline_at' => '2026-11-16 17:00:00', // overdue
        ]);
        Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'title' => 'T6 Accepted Past Deadline',
            'status' => TaskStatus::Accepted,
            'deadline_at' => '2026-11-15 17:00:00', // accepted TIDAK dihitung overdue
        ]);

        // Tugas milik intern lain (TIDAK BOLEH muncul di hitungan maupun tabel)
        Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $otherIntern->id,
            'title' => 'T7 Other Intern Overdue',
            'status' => TaskStatus::Assigned,
            'deadline_at' => '2026-11-15 17:00:00',
        ]);

        // 1. Total Tugas (6) + semua link kartu KPI tersedia
        $allResponse = $this->actingAs($intern)->get(route('intern.tasks.index'));
        $allResponse->assertOk();
        $summary = $allResponse->viewData('summary');
        $this->assertSame(6, $summary['total']);
        $this->assertSame(2, $summary['assigned']);
        $this->assertSame(2, $summary['awaiting_review']);
        $this->assertSame(1, $summary['revision_requested']);
        $this->assertSame(1, $summary['accepted']);
        $this->assertSame(2, $summary['overdue']);
        $this->assertCount(6, $allResponse->viewData('tasks'));
        $allResponse->assertDontSee('T7 Other Intern Overdue');

        // 2. Kartu Aktif Dikerjakan (assigned + in_progress = 2)
        $activeResponse = $this->actingAs($intern)->get(route('intern.tasks.index', ['status' => 'active']));
        $activeResponse->assertOk();
        $this->assertEqualsCanonicalizing(
            ['T1 Assigned Future', 'T2 InProgress Overdue'],
            $activeResponse->viewData('tasks')->pluck('title')->all()
        );

        // 3. Kartu Menunggu Review (submitted + resubmitted = 2)
        $reviewResponse = $this->actingAs($intern)->get(route('intern.tasks.index', ['status' => 'awaiting_review']));
        $reviewResponse->assertOk();
        $this->assertEqualsCanonicalizing(
            ['T3 Submitted', 'T4 Resubmitted'],
            $reviewResponse->viewData('tasks')->pluck('title')->all()
        );

        // 4. Kartu Perlu Revisi (revision_requested = 1)
        $revisionResponse = $this->actingAs($intern)->get(route('intern.tasks.index', ['status' => 'revision_requested']));
        $revisionResponse->assertOk();
        $this->assertSame(
            ['T5 Revision Overdue'],
            $revisionResponse->viewData('tasks')->pluck('title')->all()
        );

        // 5. Kartu Diterima (accepted = 1)
        $acceptedResponse = $this->actingAs($intern)->get(route('intern.tasks.index', ['status' => 'accepted']));
        $acceptedResponse->assertOk();
        $this->assertSame(
            ['T6 Accepted Past Deadline'],
            $acceptedResponse->viewData('tasks')->pluck('title')->all()
        );

        // 6. Kartu Lewat Deadline (overdue = 2; accepted & milik intern lain dikecualikan)
        $overdueResponse = $this->actingAs($intern)->get(route('intern.tasks.index', ['overdue' => 1]));
        $overdueResponse->assertOk();
        $this->assertEqualsCanonicalizing(
            ['T2 InProgress Overdue', 'T5 Revision Overdue'],
            $overdueResponse->viewData('tasks')->pluck('title')->all()
        );
    }
}
