<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TPS\StoreTPSRequest;
use App\Http\Requests\TPS\UpdateTPSRequest;
use App\Http\Resources\TPSResource;
use App\Models\TPS;
use App\Services\TPSService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TPSController extends Controller
{
    public function __construct(protected TPSService $tpsService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 10);
        $search = $request->query('search');
        $desaId = $request->query('desa_id') ?: null;

        $tpsPaginated = $this->tpsService->getPaginatedTPS($request->user(), $limit, $search, $desaId);

        return response()->json([
            'message' => 'Data TPS berhasil diambil',
            'data'    => [
                'items'      => TPSResource::collection($tpsPaginated->items()),
                'pagination' => [
                    'current_page'  => $tpsPaginated->currentPage(),
                    'total_pages'   => $tpsPaginated->lastPage(),
                    'total_records' => $tpsPaginated->total(),
                    'limit'         => $tpsPaginated->perPage(),
                    'has_next'      => $tpsPaginated->hasMorePages(),
                    'has_prev'      => $tpsPaginated->currentPage() > 1,
                ],
            ],
        ], 200);
    }

    public function byDesa(Request $request, string|int $desa_id): JsonResponse
    {
        $user = $request->user();
        if ($user->role === 'ADMIN_DESA' && (string) $user->desa_id !== (string) $desa_id) {
            return response()->json([
                'message' => 'Akses ditolak: Anda tidak memiliki izin untuk mengakses data desa lain.',
                'errors'  => ['Forbidden'],
            ], 403);
        }

        $tpsList = TPS::with(['desa', 'calonVotes.calon'])
            ->where('desa_id', $desa_id)
            ->orderBy('no_tps')
            ->get();

        return response()->json([
            'message' => 'Data TPS desa berhasil diambil',
            'data'    => TPSResource::collection($tpsList),
        ], 200);
    }

    public function store(StoreTPSRequest $request): JsonResponse
    {
        $tps = $this->tpsService->createTPS($request->validated());

        return response()->json([
            'message' => 'Data TPS berhasil ditambahkan',
            'data'    => new TPSResource($tps),
        ], 201);
    }

    public function show(TPS $tps): JsonResponse
    {
        $this->authorize('view', $tps);

        $tps->load(['desa', 'calonVotes.calon']);

        return response()->json([
            'message' => 'Data TPS berhasil diambil',
            'data'    => new TPSResource($tps),
        ], 200);
    }

    public function update(UpdateTPSRequest $request, TPS $tps): JsonResponse
    {
        $this->authorize('update', $tps);

        $updatedTPS = $this->tpsService->updateTPS($tps, $request->validated());

        return response()->json([
            'message' => 'Data TPS berhasil diperbarui',
            'data'    => new TPSResource($updatedTPS),
        ], 200);
    }

    public function destroy(TPS $tps): JsonResponse
    {
        $this->authorize('delete', $tps);

        $this->tpsService->deleteTPS($tps);

        return response()->json([
            'message' => 'Data TPS berhasil dihapus',
        ], 200);
    }
}
