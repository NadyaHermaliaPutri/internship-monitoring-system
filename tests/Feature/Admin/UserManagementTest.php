<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
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
            'name' => 'Peserta Magang',
            'email' => 'intern@ptwan.com',
            'role' => 'intern',
            'is_active' => true,
        ]);
    }

    public function test_guest_cannot_access_user_management(): void
    {
        $response = $this->get('/admin/users');
        $response->assertRedirect('/login');
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        // PIC dilarang
        $response = $this->actingAs($this->pic)->get('/admin/users');
        $response->assertStatus(403);

        // Intern dilarang
        $response = $this->actingAs($this->intern)->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_admin_can_view_users_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('Kelola Akun');
        $response->assertSee($this->admin->email);
        $response->assertSee($this->pic->email);
        $response->assertSee($this->intern->email);
    }

    public function test_admin_can_filter_users_by_role_and_search(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/users?role=intern&search=Peserta');

        $response->assertStatus(200);
        $response->assertSee('Peserta Magang');
    }

    public function test_admin_can_view_create_user_form(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/users/create');

        $response->assertStatus(200);
        $response->assertSee('Tambah Akun Baru');
    }

    public function test_admin_can_store_new_user_with_hashed_password(): void
    {
        $userData = [
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@ptwan.com',
            'role' => 'intern',
            'is_active' => '1',
            'password' => 'Rahasia123#',
            'password_confirmation' => 'Rahasia123#',
        ];

        $response = $this->actingAs($this->admin)->post('/admin/users', $userData);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@ptwan.com',
            'role' => 'intern',
            'is_active' => true,
        ]);

        $createdUser = User::where('email', 'budi.santoso@ptwan.com')->first();
        $this->assertTrue(Hash::check('Rahasia123#', $createdUser->password));
    }

    public function test_user_creation_validates_required_fields_and_unique_email(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/users', [
            'name' => '',
            'email' => 'admin@ptwan.com', // email sudah ada
            'role' => 'invalid_role',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'role', 'password']);
    }

    public function test_admin_can_view_edit_user_form(): void
    {
        $response = $this->actingAs($this->admin)->get("/admin/users/{$this->intern->id}/edit");

        $response->assertStatus(200);
        $response->assertSee($this->intern->name);
        $response->assertSee($this->intern->email);
    }

    public function test_admin_can_update_user_without_changing_password(): void
    {
        $oldPasswordHash = $this->intern->password;

        $response = $this->actingAs($this->admin)->put("/admin/users/{$this->intern->id}", [
            'name' => 'Peserta Magang Updated',
            'email' => 'intern.updated@ptwan.com',
            'role' => 'intern',
            'is_active' => '1',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $this->intern->refresh();
        $this->assertEquals('Peserta Magang Updated', $this->intern->name);
        $this->assertEquals('intern.updated@ptwan.com', $this->intern->email);
        $this->assertEquals($oldPasswordHash, $this->intern->password);
    }

    public function test_admin_cannot_deactivate_own_active_account(): void
    {
        $response = $this->actingAs($this->admin)->put("/admin/users/{$this->admin->id}", [
            'name' => 'Admin HR PT WAN',
            'email' => 'admin@ptwan.com',
            'role' => 'admin',
            'is_active' => '0', // mencoba nonaktifkan akun sendiri
        ]);

        $response->assertSessionHasErrors('is_active');
        $this->admin->refresh();
        $this->assertTrue($this->admin->is_active);
    }

    public function test_admin_can_toggle_intern_status_instantly(): void
    {
        $this->assertTrue($this->intern->is_active);

        $response = $this->actingAs($this->admin)->patch("/admin/users/{$this->intern->id}/toggle-status");

        $response->assertSessionHas('success');
        $this->intern->refresh();
        $this->assertFalse($this->intern->is_active);

        // Toggle kembali ke aktif
        $this->actingAs($this->admin)->patch("/admin/users/{$this->intern->id}/toggle-status");
        $this->intern->refresh();
        $this->assertTrue($this->intern->is_active);
    }
}
