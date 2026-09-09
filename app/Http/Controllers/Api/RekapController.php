<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Services\RekapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RekapController extends Controller
{
    public function __construct(protected RekapService $rekapService)
    {
    }

    /**
     * Get recap based on current user / optional desa_id query.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'ADMIN_DESA') {
            $desa = Desa::findOrFail($user->desa_id);
            return response()->json([
                'message' => 'Rekapitulasi desa berhasil diambil',
                'data'    => $this->rekapService->getDesaRecap($desa),
            ], 200);
        }

        // ADMIN_PUSAT
        if ($request->filled('desa_id')) {
            $desa = Desa::findOrFail($request->query('desa_id'));
            return response()->json([
                'message' => 'Rekapitulasi desa berhasil diambil',
                'data'    => $this->rekapService->getDesaRecap($desa),
            ], 200);
        }

        $search = $request->query('search');
        $page = $request->has('page') ? (int) $request->query('page', 1) : null;
        $limit = (int) $request->query('limit', 10);

        return response()->json([
            'message' => 'Rekapitulasi seluruh pemilihan berhasil diambil',
            'data'    => $this->rekapService->getAllDesaRecap($search, $page, $limit),
        ], 200);
    }

    /**
     * Get cached recap for all villages.
     */
    public function all(): JsonResponse
    {
        return response()->json([
            'message' => 'Rekapitulasi seluruh desa berhasil diambil',
            'data'    => $this->rekapService->getAllDesaRecap(),
        ], 200);
    }

    /**
     * Get recap for a specific desa.
     */
    public function show(Request $request, string|int $desa_id): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'ADMIN_DESA' && (string) $user->desa_id !== (string) $desa_id) {
            return response()->json([
                'message' => 'Akses ditolak: Anda tidak memiliki izin untuk mengakses data desa lain.',
                'errors'  => ['Forbidden'],
            ], 403);
        }

        $desa = Desa::findOrFail($desa_id);

        return response()->json([
            'message' => 'Rekapitulasi desa berhasil diambil',
            'data'    => $this->rekapService->getDesaRecap($desa),
        ], 200);
    }
}
