<?php

namespace Tests\Feature\Dashboard;

use App\Enums\LogbookStatus;
use App\Enums\TaskStatus;
use App\Models\Logbook;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InternDashboardTest extends TestCase
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

    public function test_intern_dashboard_displays_real_tasks_and_notifications_without_dummy_data_or_leakage(): void
    {
        $pic = User::factory()->create(['role' => 'pic', 'name' => 'Budi Pembimbing', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $otherIntern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        // Tugas milik intern yang login (perlu revisi)
        $myTask = Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'title' => 'Integrasi Modul Notifikasi Asli',
            'status' => TaskStatus::RevisionRequested,
            'deadline_at' => Carbon::create(2026, 11, 27, 17, 0, 0, 'Asia/Jakarta'),
        ]);

        // Logbook manual milik intern yang perlu revisi
        $myLogbook = Logbook::factory()->revisionRequested($pic, 'Lengkapi screenshot hasil pengujian.')->create([
            'intern_user_id' => $intern->id,
            'log_date' => '2026-11-26',
            'title' => 'Logbook Pengujian Header Bell',
        ]);

        // Tugas & logbook milik peserta LAIN (tidak boleh bocor)
        Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $otherIntern->id,
            'title' => 'Tugas Rahasia Peserta Lain',
            'status' => TaskStatus::Assigned,
            'deadline_at' => Carbon::create(2026, 11, 27, 12, 0, 0, 'Asia/Jakarta'),
        ]);

        Logbook::factory()->revisionRequested($pic, 'Catatan peserta lain.')->create([
            'intern_user_id' => $otherIntern->id,
            'log_date' => '2026-11-26',
            'title' => 'Logbook Rahasia Peserta Lain',
        ]);

        $response = $this->actingAs($intern)->get(route('intern.dashboard'));
        $response->assertOk();

        // Tidak ada lagi teks dummy statis
        $response->assertDontSee('Fadly Husaeni');
        $response->assertDontSee('ExpressJS');
        $response->assertDontSee('Yustyar');

        // Data asli milik peserta tampil pada dashboard & dropdown lonceng header
        $response->assertSee('Integrasi Modul Notifikasi Asli');
        $response->assertSee('Budi Pembimbing');
        $response->assertSee('Logbook Pengujian Header Bell');
        $response->assertSee(route('intern.tasks.show', $myTask), false);
        $response->assertSee(route('intern.logbooks.show', $myLogbook), false);
        $response->assertSee('id="header-notification-dropdown"', false);

        // Tidak membocorkan data peserta lain
        $response->assertDontSee('Tugas Rahasia Peserta Lain');
        $response->assertDontSee('Logbook Rahasia Peserta Lain');
    }

    public function test_pic_and_admin_dashboards_render_successfully_with_header_notifications(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);

        DB::table('pic_assignments')->insert([
            'intern_user_id' => $intern->id,
            'pic_user_id' => $pic->id,
            'start_date' => '2026-11-01',
            'end_date' => null,
            'assigned_by' => $admin->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Task::factory()->create([
            'pic_user_id' => $pic->id,
            'intern_user_id' => $intern->id,
            'title' => 'Tugas Menunggu Review PIC',
            'status' => TaskStatus::Submitted,
            'deadline_at' => Carbon::create(2026, 11, 25, 17, 0, 0, 'Asia/Jakarta'),
        ]);

        Logbook::factory()->create([
            'intern_user_id' => $intern->id,
            'target_pic_user_id' => $pic->id,
            'log_date' => '2026-11-26',
            'title' => 'Logbook Pending Untuk PIC',
            'status' => LogbookStatus::Pending,
        ]);

        $picResponse = $this->actingAs($pic)->get(route('pic.dashboard'));
        $picResponse->assertOk();
        $picResponse->assertSee('id="header-notification-dropdown"', false);
        $picResponse->assertSee('Tugas Menunggu Review PIC');
        $picResponse->assertSee('Logbook Pending Untuk PIC');

        $adminResponse = $this->actingAs($admin)->get(route('admin.dashboard'));
        $adminResponse->assertOk();
        $adminResponse->assertSee('id="header-notification-dropdown"', false);
    }
}
