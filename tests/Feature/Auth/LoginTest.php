<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Sistem Monitoring Magang');
    }

    public function test_admin_user_can_authenticate_and_redirect_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@ptwan.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@ptwan.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect('/admin/dashboard');
    }

    public function test_pic_user_can_authenticate_and_redirect_to_pic_dashboard(): void
    {
        $pic = User::factory()->create([
            'email' => 'pic@ptwan.com',
            'role' => 'pic',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'pic@ptwan.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($pic);
        $response->assertRedirect('/pic/dashboard');
    }

    public function test_intern_user_can_authenticate_and_redirect_to_intern_dashboard(): void
    {
        $intern = User::factory()->create([
            'email' => 'intern@ptwan.com',
            'role' => 'intern',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'intern@ptwan.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($intern);
        $response->assertRedirect('/intern/dashboard');
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        User::factory()->create([
            'email' => 'intern.nonaktif@ptwan.com',
            'role' => 'intern',
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'intern.nonaktif@ptwan.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'admin@ptwan.com',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@ptwan.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_user_can_logout_and_session_is_invalidated(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public function test_user_cannot_access_other_role_dashboard(): void
    {
        $intern = User::factory()->create([
            'role' => 'intern',
            'is_active' => true,
        ]);

        // Peserta magang dilarang mengakses dashboard admin
        $response = $this->actingAs($intern)->get('/admin/dashboard');
        $response->assertStatus(403);
    }
}
