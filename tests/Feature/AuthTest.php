<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\UserSeeder::class);
    }

    public function test_unauthenticated_user_redirected_to_login()
    {
        $response = $this->get('/contracts');
        $response->assertRedirect('/login');
    }

    public function test_supervisor_can_login_with_demo_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'supervisor@mail.com',
            'password' => 'supervisor',
        ]);

        $response->assertRedirect('/contracts');
        $this->assertAuthenticated();
        $this->assertEquals('supervisor', auth()->user()->role);
    }

    public function test_staff_can_login_with_demo_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'staff@mail.com',
            'password' => 'staff123',
        ]);

        $response->assertRedirect('/contracts');
        $this->assertAuthenticated();
        $this->assertEquals('staff', auth()->user()->role);
    }

    public function test_user_cannot_login_with_wrong_password()
    {
        $response = $this->post('/login', [
            'email' => 'staff@mail.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout()
    {
        $user = User::where('email', 'staff@mail.com')->first();
        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
