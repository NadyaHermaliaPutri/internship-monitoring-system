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

    public function test_creating_task_validates_target_intern_role_active_status_pic_assignment_and_future_deadline(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $unassignedIntern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $inactiveIntern = User::factory()->create(['role' => 'intern', 'is_active' => false]);
        $expiredAssignmentIntern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        $this->assignInternToPic($pic, $inactiveIntern);
        $this->assignInternToPic($pic, $expiredAssignmentIntern, '2026-10-01', '2026-10-31');

        // 1. Peserta belum pernah ditugaskan ke PIC ini
        $this->actingAs($pic)->post(route('pic.tasks.store'), [
            'intern_user_id' => $unassignedIntern->id,
            'title' => 'Tugas Uji',
            'description' => 'Deskripsi tugas uji validasi peserta.',
            'deadline_at' => '2026-11-20 17:00:00',
        ])->assertSessionHasErrors('intern_user_id');

        // 2. Peserta nonaktif (is_active = false)
        $this->actingAs($pic)->post(route('pic.tasks.store'), [
            'intern_user_id' => $inactiveIntern->id,
            'title' => 'Tugas Uji',
            'description' => 'Deskripsi tugas uji validasi peserta nonaktif.',
            'deadline_at' => '2026-11-20 17:00:00',
        ])->assertSessionHasErrors('intern_user_id');

        // 3. Periode pic_assignments sudah berakhir
        $this->actingAs($pic)->post(route('pic.tasks.store'), [
            'intern_user_id' => $expiredAssignmentIntern->id,
            'title' => 'Tugas Uji',
            'description' => 'Deskripsi tugas uji periode berakhir.',
            'deadline_at' => '2026-11-20 17:00:00',
        ])->assertSessionHasErrors('intern_user_id');

        // 4. Deadline di masa lalu (<= sekarang 10:00 WIB)
        $validIntern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $this->assignInternToPic($pic, $validIntern);

        $this->actingAs($pic)->post(route('pic.tasks.store'), [
            'intern_user_id' => $validIntern->id,
            'title' => 'Tugas Uji Deadline Lewat',
            'description' => 'Deskripsi tugas uji deadline masa lalu.',
            'deadline_at' => '2026-11-18 09:30:00',
        ])->assertSessionHasErrors('deadline_at');

        $this->assertDatabaseCount('tasks', 0);
        $this->assertDatabaseCount('logbooks', 0);
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
}
