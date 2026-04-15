<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_auth_pages(): void
    {
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();
    }

    public function test_client_registration_creates_account_and_redirects(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Client Demo',
            'email' => 'client.demo@gmail.com',
            'phone' => '900000123',
            'role' => 'client',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'client.demo@gmail.com',
            'role' => 'client',
            'approval_status' => 'approved',
        ]);
    }

    public function test_role_middleware_blocks_wrong_space(): void
    {
        $user = User::factory()->create([
            'role' => 'client',
            'approval_status' => 'approved',
        ]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }
}