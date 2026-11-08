<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_a_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Registrasi berhasil')
            ->assertJsonPath('user.email', 'budi@example.com')
            ->assertJsonMissingPath('user.password')
            ->assertJsonStructure(['token', 'token_type']);

        $this->assertDatabaseHas('users', ['email' => 'budi@example.com']);
    }

    public function test_user_can_log_in_view_profile_and_log_out(): void
    {
        User::factory()->create([
            'email' => 'reader@example.com',
            'password' => 'password123',
        ]);

        $this->postJson('/api/login', [
            'email' => 'reader@example.com',
            'password' => 'wrong-password',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $login = $this->postJson('/api/login', [
            'email' => 'reader@example.com',
            'password' => 'password123',
            'device_name' => 'feature-test',
        ])->assertOk()
            ->assertJsonPath('user.email', 'reader@example.com')
            ->assertJsonPath('token_type', 'Bearer');

        $token = $login->json('token');

        $this->withToken($token)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'reader@example.com');

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logout berhasil');

        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/me')->assertUnauthorized();
    }

    public function test_registration_validates_required_fields(): void
    {
        $this->postJson('/api/register', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }
}
