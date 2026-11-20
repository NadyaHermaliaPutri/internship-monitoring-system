<?php

namespace Tests\Feature\Admin;

use App\Models\InternProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $pic;
    private User $intern;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin HR PT WAN',
            'email' => 'admin@ptwan.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->pic = User::factory()->create([
            'name' => 'PIC Staff',
            'email' => 'pic@ptwan.com',
            'role' => 'pic',
            'is_active' => true,
        ]);

        $this->intern = User::factory()->create([
            'name' => 'Nadya Hermalia',
            'email' => 'nadya@pnp.ac.id',
            'role' => 'intern',
            'is_active' => true,
        ]);
    }

    public function test_guest_cannot_access_intern_profiles(): void
    {
        $response = $this->get('/admin/intern-profiles');
        $response->assertRedirect('/login');
    }

    public function test_non_admin_cannot_access_intern_profiles(): void
    {
        $response = $this->actingAs($this->intern)->get('/admin/intern-profiles');
        $response->assertForbidden();

        $responsePic = $this->actingAs($this->pic)->get('/admin/intern-profiles');
        $responsePic->assertForbidden();
    }

    public function test_admin_can_view_intern_profiles_list(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/intern-profiles');
        $response->assertOk();
        $response->assertSee('Nadya Hermalia');
        $response->assertSee('Daftar Profil Peserta Magang');
    }

    public function test_admin_can_view_edit_profile_form_for_intern(): void
    {
        $response = $this->actingAs($this->admin)->get("/admin/intern-profiles/{$this->intern->id}/edit");
        $response->assertOk();
        $response->assertSee('Nadya Hermalia');
        $response->assertSee('Asal Institusi / Universitas');
    }

    public function test_admin_cannot_edit_intern_profile_for_non_intern_user(): void
    {
        $response = $this->actingAs($this->admin)->get("/admin/intern-profiles/{$this->pic->id}/edit");
        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('error');
    }

    public function test_admin_can_save_intern_profile(): void
    {
        $response = $this->actingAs($this->admin)->put("/admin/intern-profiles/{$this->intern->id}", [
            'intern_code' => 'INT-2026-001',
            'institution' => 'Politeknik Negeri Padang',
            'study_program' => 'Teknologi Informasi',
            'internship_start_date' => '2026-09-01',
            'internship_end_date' => '2026-11-30',
        ]);

        $response->assertRedirect('/admin/intern-profiles');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('intern_profiles', [
            'user_id' => $this->intern->id,
            'intern_code' => 'INT-2026-001',
            'institution' => 'Politeknik Negeri Padang',
            'study_program' => 'Teknologi Informasi',
            'internship_start_date' => '2026-09-01',
            'internship_end_date' => '2026-11-30',
        ]);
    }

    public function test_start_date_is_required(): void
    {
        $response = $this->actingAs($this->admin)->put("/admin/intern-profiles/{$this->intern->id}", [
            'institution' => 'Politeknik Negeri Padang',
            'internship_start_date' => '',
        ]);

        $response->assertSessionHasErrors(['internship_start_date']);
    }

    public function test_end_date_cannot_be_before_start_date(): void
    {
        $response = $this->actingAs($this->admin)->put("/admin/intern-profiles/{$this->intern->id}", [
            'internship_start_date' => '2026-09-01',
            'internship_end_date' => '2026-08-01',
        ]);

        $response->assertSessionHasErrors(['internship_end_date']);
    }
}
