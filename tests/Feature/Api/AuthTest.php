<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'username' => 'testadmin',
            'password' => Hash::make('secret123'),
            'status'   => 'aktif',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'testadmin',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'token',
                    'expires_in',
                    'user' => ['id', 'username', 'role', 'status'],
                ],
            ])
            ->assertJsonPath('data.expires_in', 604800);

        $jwt = $response->json('data.token');
        $this->assertNotEmpty($jwt);

        $payload = app(\App\Services\JwtService::class)->verify($jwt);
        $this->assertNotNull($payload);
        $this->assertEquals((string) $user->id, $payload['sub']);
        $this->assertEquals($user->role, $payload['role']);
        $this->assertTrue($payload['exp'] > time());
        $daysUntilExp = (int) round(($payload['exp'] - time()) / 86400);
        $this->assertEquals(7, $daysUntilExp);
    }

    public function test_user_can_login_with_email(): void
    {
        $user = User::factory()->create([
            'email'    => 'custom@tabanan.go.id',
            'password' => Hash::make('secret123'),
            'status'   => 'aktif',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'custom@tabanan.go.id',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.user.email', 'custom@tabanan.go.id');
    }

    public function test_login_returns_standardized_error_without_leaking_user_existence(): void
    {
        $user = User::factory()->create([
            'username' => 'existinguser',
            'password' => Hash::make('correctpassword'),
            'status'   => 'aktif',
        ]);

        // Wrong password for existing user
        $wrongPassResponse = $this->postJson('/api/v1/auth/login', [
            'username' => 'existinguser',
            'password' => 'wrongpassword',
        ]);
        $wrongPassResponse->assertStatus(401)
            ->assertJson([
                'message' => 'Username atau password salah',
                'errors'  => ['Unauthorized'],
            ]);

        // Non-existent user gets the EXACT same error response
        $nonExistentResponse = $this->postJson('/api/v1/auth/login', [
            'username' => 'nonexistent_account_123',
            'password' => 'somepassword',
        ]);
        $nonExistentResponse->assertStatus(401)
            ->assertJson([
                'message' => 'Username atau password salah',
                'errors'  => ['Unauthorized'],
            ]);
    }

    public function test_nonaktif_user_is_rejected_on_login(): void
    {
        $user = User::factory()->create([
            'username' => 'inactiveuser',
            'password' => Hash::make('secret123'),
            'status'   => 'nonaktif',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'inactiveuser',
            'password' => 'secret123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Akun anda telah dinonaktifkan. Silakan hubungi Admin Pusat',
            ]);
    }

    public function test_authenticated_user_can_view_profile_and_logout_with_jwt(): void
    {
        $user = User::factory()->create(['status' => 'aktif']);
        $jwt = app(\App\Services\JwtService::class)->generateToken($user);

        // Profile access with JWT
        $profileResponse = $this->withHeader('Authorization', "Bearer {$jwt}")
            ->getJson('/api/v1/auth/profile');

        $profileResponse->assertStatus(200)
            ->assertJsonPath('data.username', $user->username);

        // Logout with JWT
        $logoutResponse = $this->withHeader('Authorization', "Bearer {$jwt}")
            ->postJson('/api/v1/auth/logout');

        $logoutResponse->assertStatus(200);

        // Subsequent access with revoked token should fail with 401
        $revokedResponse = $this->withHeader('Authorization', "Bearer {$jwt}")
            ->getJson('/api/v1/auth/profile');

        $revokedResponse->assertStatus(401);
    }
}
