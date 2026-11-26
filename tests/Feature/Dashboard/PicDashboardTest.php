<?php

namespace Tests\Feature\Dashboard;

use App\Enums\TaskStatus;
use App\Models\InternProfile;
use App\Models\Task;
use App\Models\TaskSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PicDashboardTest extends TestCase
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

    public function test_pic_dashboard_is_only_accessible_by_pic_role(): void
    {
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($intern)->get(route('pic.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('pic.dashboard'))->assertForbidden();
    }

    public function test_pic_dashboard_displays_only_own_scoped_real_data_without_dummy_text_or_other_pic_leakage(): void
    {
        $picA = User::factory()->create(['role' => 'pic', 'name' => 'PIC Utama', 'is_active' => true]);
        $picB = User::factory()->create(['role' => 'pic', 'name' => 'PIC Lain', 'is_active' => true]);
        $internA = User::factory()->create(['role' => 'intern', 'name' => 'Rizky Peserta', 'is_active' => true]);
        $internB = User::factory()->create(['role' => 'intern', 'name' => 'Dina Peserta Lain', 'is_active' => true]);

        InternProfile::create([
            'user_id' => $internA->id,
            'intern_code' => 'INT-001',
            'institution' => 'Universitas Andalas',
            'study_program' => 'Teknik Informatika',
            'internship_start_date' => '2026-10-01',
            'internship_end_date' => '2026-12-31',
        ]);

        // Tugas buatan PIC A yang submitted (menunggu review PIC A)
        $mySubmittedTask = Task::factory()->create([
            'pic_user_id' => $picA->id,
            'intern_user_id' => $internA->id,
            'title' => 'Integrasi Dashboard PIC Asli',
            'status' => TaskStatus::Submitted,
            'deadline_at' => Carbon::create(2026, 11, 27, 17, 0, 0, 'Asia/Jakarta'),
        ]);

        TaskSubmission::create([
            'task_id' => $mySubmittedTask->id,
            'submitted_by' => $internA->id,
            'submission_number' => 1,
            'submission_url' => 'https://github.com/wan/pic-dashboard',
            'submitted_at' => Carbon::create(2026, 11, 26, 8, 30, 0, 'Asia/Jakarta'),
        ]);

        // Tugas buatan PIC B (TIDAK BOLEH bocor ke dashboard PIC A)
        Task::factory()->create([
            'pic_user_id' => $picB->id,
            'intern_user_id' => $internB->id,
            'title' => 'Tugas Rahasia Milik PIC B',
            'status' => TaskStatus::Submitted,
            'deadline_at' => Carbon::create(2026, 11, 26, 15, 0, 0, 'Asia/Jakarta'),
        ]);

        $response = $this->actingAs($picA)->get(route('pic.dashboard'));
        $response->assertOk();

        // Tidak ada lagi teks dummy statis
        $response->assertDontSee('Nadya Hermalia');
        $response->assertDontSee('Mutiara F. R.');
        $response->assertDontSee('Atika Naira');
        $response->assertDontSee('Politeknik Negeri Padang');
        $response->assertDontSee('[p.8]');
        $response->assertDontSee('Buka Chat / Diskusi');

        // Data asli milik PIC A tampil lengkap dengan tautan route nyata
        $response->assertSee('Integrasi Dashboard PIC Asli');
        $response->assertSee('Rizky Peserta');
        $response->assertSee('Universitas Andalas');
        $response->assertSee(route('pic.tasks.show', $mySubmittedTask), false);
        $response->assertSee(route('pic.tasks.create'), false);

        // Tidak membocorkan tugas & peserta milik PIC B
        $response->assertDontSee('Tugas Rahasia Milik PIC B');
        $response->assertDontSee('Dina Peserta Lain');
    }
}
