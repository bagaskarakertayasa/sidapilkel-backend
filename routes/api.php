<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CalonController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DesaController;
use App\Http\Controllers\Api\RekapController;
use App\Http\Controllers\Api\TPSController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn() => response()->json(['status' => 'healthy', 'version' => '2.0.0']));

Route::prefix('v1')->group(function () {
    // 1. Auth Publik
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:10,5');

    // 2. Endpoint Terproteksi (JWT Auth + Active Status)
    Route::middleware(['jwt.auth', 'account.active'])->group(function () {
        // Autentikasi User
        Route::get('/auth/profile', [AuthController::class, 'profile']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Dashboard & Rekapitulasi (Custom Business Endpoints)
        Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
        Route::get('/rekap', [RekapController::class, 'index']);
        Route::get('/rekap/all', [RekapController::class, 'all']);
        Route::get('/rekap/{desa_id}', [RekapController::class, 'show']);

        // Master Desa (Dropdown Simple)
        Route::get('/desa/simple', [DesaController::class, 'simple']);

        // Calon Custom Actions
        Route::get('/calon/desa/{desa_id}', [CalonController::class, 'byDesa']);
        Route::post('/calon/upload-photo', [CalonController::class, 'uploadPhoto']);
        Route::post('/calon/batch', [CalonController::class, 'batchStore']);
        Route::delete('/calon/reset/{desa_id}', [CalonController::class, 'resetByDesa']);

        // TPS Custom Actions
        Route::get('/tps/desa/{desa_id}', [TPSController::class, 'byDesa']);

        // Resourceful CRUD (Best Practice #1)
        Route::apiResource('users', UserController::class);
        Route::patch('/users/{id}/password', [UserController::class, 'updatePassword']);
        Route::patch('/users/{id}/status', [UserController::class, 'toggleStatus']);

        Route::apiResource('desa', DesaController::class);
        Route::apiResource('calon', CalonController::class)->except(['index']);
        Route::apiResource('tps', TPSController::class)->parameters(['tps' => 'tps']);
    });
});
