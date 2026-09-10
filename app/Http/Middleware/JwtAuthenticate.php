<?php

namespace App\Http\Middleware;

use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthenticate
{
    /**
     * Handle an incoming request using JWT authentication.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?? $request->cookie('sidapilkel_token');

        // If no token is provided, check if user is already authenticated (e.g. via actingAs in tests)
        if (!$token) {
            if (Auth::check()) {
                return $next($request);
            }

            return response()->json([
                'message' => 'Unauthenticated. Token JWT tidak ditemukan.',
                'errors'  => ['Unauthorized'],
            ], 401);
        }

        $jwtService = app(JwtService::class);
        $user = $jwtService->validateAndGetUser($token);

        if (!$user) {
            return response()->json([
                'message' => 'Token JWT tidak valid atau telah kadaluarsa.',
                'errors'  => ['Unauthorized'],
            ], 401);
        }

        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
