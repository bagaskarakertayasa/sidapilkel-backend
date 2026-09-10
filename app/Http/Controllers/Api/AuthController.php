<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * User Login.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $loginInput = trim((string) ($request->input('username') ?: $request->input('email')));

        $user = User::where('username', $loginInput)
            ->orWhere('email', $loginInput)
            ->first();

        if (!$user || !Hash::check((string) $request->password, $user->password)) {
            return response()->json([
                'message' => 'Username atau password salah',
                'errors'  => ['Unauthorized'],
            ], 401);
        }

        if (!$user->isAktif()) {
            return response()->json([
                'message' => 'Akun anda telah dinonaktifkan. Silakan hubungi Admin Pusat',
                'errors'  => ['Forbidden'],
            ], 403);
        }

        $jwtService = app(\App\Services\JwtService::class);
        $token = $jwtService->generateToken($user);

        $cookieMinutes = (int) ceil(604800 / 60); // 1 week in minutes
        $tokenCookie = cookie(
            'sidapilkel_token',
            $token,
            $cookieMinutes,
            '/',
            null,
            $request->isSecure(),
            true,   // HttpOnly — JavaScript cannot read this cookie
            false,
            'Lax'
        );

        return response()->json([
            'message' => 'Login berhasil',
            'data'    => [
                'expires_in' => 604800,
                'user'       => new UserResource($user->load('desa')),
            ],
        ], 200)->withCookie($tokenCookie);
    }

    /**
     * Get authenticated user profile.
     */
    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Profil pengguna berhasil diambil',
            'data'    => new UserResource($request->user()->load('desa')),
        ], 200);
    }

    /**
     * User Logout.
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->bearerToken() ?? $request->cookie('sidapilkel_token');
        if ($token) {
            app(\App\Services\JwtService::class)->revokeToken($token);
        }

        \Illuminate\Support\Facades\Auth::forgetGuards();

        $clearCookie = cookie()->forget('sidapilkel_token');

        return response()->json([
            'message' => 'Logout berhasil',
        ], 200)->withCookie($clearCookie);
    }
}
