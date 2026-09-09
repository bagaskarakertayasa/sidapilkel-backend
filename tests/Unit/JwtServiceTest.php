<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JwtServiceTest extends TestCase
{
    use RefreshDatabase;

    private JwtService $jwt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwt = app(JwtService::class);
    }

    public function test_can_generate_and_verify_valid_jwt(): void
    {
        $user = User::factory()->create(['status' => 'aktif']);

        $token = $this->jwt->generateToken($user);
        $this->assertIsString($token);

        $parts = explode('.', $token);
        $this->assertCount(3, $parts);

        $payload = $this->jwt->verify($token);
        $this->assertNotNull($payload);
        $this->assertEquals((string) $user->id, $payload['sub']);
        $this->assertEquals($user->role, $payload['role']);
        $this->assertGreaterThan(time(), $payload['exp']);
    }

    public function test_rejects_tampered_token(): void
    {
        $user = User::factory()->create();
        $token = $this->jwt->generateToken($user);

        $parts = explode('.', $token);
        // Tamper with payload
        $tamperedToken = $parts[0] . '.' . base64_encode('{"sub":"fake"}') . '.' . $parts[2];

        $this->assertNull($this->jwt->verify($tamperedToken));
    }

    public function test_rejects_expired_token(): void
    {
        $user = User::factory()->create();
        // Generate with negative TTL (-10 seconds)
        $token = $this->jwt->generateToken($user, -10);

        $this->assertNull($this->jwt->verify($token));
    }

    public function test_rejects_revoked_token(): void
    {
        $user = User::factory()->create();
        $token = $this->jwt->generateToken($user);

        $this->assertNotNull($this->jwt->verify($token));

        $this->jwt->revokeToken($token);

        $this->assertNull($this->jwt->verify($token));
    }
}
