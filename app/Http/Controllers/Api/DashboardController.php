<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RekapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected RekapService $rekapService)
    {
    }

    /**
     * Get dashboard statistics based on role.
     */
    public function getStats(Request $request): JsonResponse
    {
        $stats = $this->rekapService->getDashboardStats($request->user(), $request);

        return response()->json([
            'message' => 'Statistik dashboard berhasil diambil',
            'data'    => $stats,
        ], 200);
    }
}
