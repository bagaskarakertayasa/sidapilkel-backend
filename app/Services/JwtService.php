<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class JwtService
{
    private string $secret;
    private string $issuer;

    public function __construct()
    {
        $key = config('app.key');
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }
        if (empty($key)) {
            throw new \RuntimeException('APP_KEY harus di-set. Jalankan: php artisan key:generate');
        }
        $this->secret = $key;
        $this->issuer = (string) config('app.url', 'sidapilkel');
    }

    /**
     * Generate a new RFC 7519 compliant HS256 JSON Web Token.
     * Default TTL: 604,800 seconds (1 week / 7 days).
     */
    public function generateToken(User $user, int $ttl = 604800): string
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];

        $now = time();
        $payload = [
            'iss' => $this->issuer,
            'sub' => (string) $user->id,
            'iat' => $now,
            'exp' => $now + $ttl,
            'jti' => bin2hex(random_bytes(16)),
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES));
        $signature = hash_hmac('sha256', "{$headerEncoded}.{$payloadEncoded}", $this->secret, true);
        $signatureEncoded = $this->base64UrlEncode($signature);

        return "{$headerEncoded}.{$payloadEncoded}.{$signatureEncoded}";
    }

    /**
     * Verify and decode a JWT token. Returns payload array if valid, null otherwise.
     */
    public function verify(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        // Verify signature
        $expectedSignature = hash_hmac('sha256', "{$headerEncoded}.{$payloadEncoded}", $this->secret, true);
        $actualSignature = $this->base64UrlDecode($signatureEncoded);

        if (!hash_equals($expectedSignature, $actualSignature)) {
            return null;
        }

        $header = json_decode($this->base64UrlDecode($headerEncoded), true);
        if (!is_array($header) || ($header['alg'] ?? '') !== 'HS256') {
            return null;
        }

        $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);
        if (!is_array($payload)) {
            return null;
        }

        // Check expiration
        if (!isset($payload['exp']) || time() > $payload['exp']) {
            return null;
        }

        // Check revocation (persistent DB-based blacklist)
        if (!empty($payload['jti']) && DB::table('revoked_tokens')->where('jti', $payload['jti'])->exists()) {
            return null;
        }

        return $payload;
    }

    /**
     * Validate token and return the User model.
     */
    public function validateAndGetUser(string $token): ?User
    {
        $payload = $this->verify($token);
        if (!$payload || empty($payload['sub'])) {
            return null;
        }

        return User::find($payload['sub']);
    }

    /**
     * Revoke a token by inserting its jti into the persistent revoked_tokens table.
     */
    public function revokeToken(string $token): bool
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        $payload = json_decode($this->base64UrlDecode($parts[1]), true);
        if (!is_array($payload) || empty($payload['jti'])) {
            return false;
        }

        $exp = $payload['exp'] ?? (time() + 604800);

        DB::table('revoked_tokens')->insertOrIgnore([
            'jti'        => $payload['jti'],
            'expires_at' => date('Y-m-d H:i:s', $exp),
            'created_at' => now(),
        ]);

        return true;
    }

    /**
     * Cleanup expired revoked tokens (call via scheduler or artisan command).
     */
    public function cleanupExpiredTokens(): int
    {
        return DB::table('revoked_tokens')->where('expires_at', '<', now())->delete();
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }
}
