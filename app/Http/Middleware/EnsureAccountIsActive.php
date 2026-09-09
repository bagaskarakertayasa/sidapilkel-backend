<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->status !== 'aktif') {
            return response()->json([
                'message' => 'Akun anda telah dinonaktifkan. Silakan hubungi Admin Pusat',
                'errors'  => ['Forbidden'],
            ], 403);
        }

        return $next($request);
    }
}
