<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Desa\StoreDesaRequest;
use App\Http\Requests\Desa\UpdateDesaRequest;
use App\Http\Resources\DesaResource;
use App\Models\Desa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DesaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Desa::class);

        $limit = (int) $request->query('limit', 10);
        $search = $request->query('search');

        $query = Desa::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_desa', 'like', "%{$search}%")
                  ->orWhere('kecamatan', 'like', "%{$search}%");
            });
        }

        $desaPaginated = $query->orderBy('kecamatan')->orderBy('nama_desa')->paginate($limit);

        return response()->json([
            'message' => 'Data desa berhasil diambil',
            'data'    => [
                'items'      => DesaResource::collection($desaPaginated->items()),
                'pagination' => [
                    'current_page'  => $desaPaginated->currentPage(),
                    'total_pages'   => $desaPaginated->lastPage(),
                    'total_records' => $desaPaginated->total(),
                    'limit'         => $desaPaginated->perPage(),
                    'has_next'      => $desaPaginated->hasMorePages(),
                    'has_prev'      => $desaPaginated->currentPage() > 1,
                ],
            ],
        ], 200);
    }

    public function simple(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $query = Desa::select('id', 'nama_desa', 'kecamatan')
            ->orderBy('kecamatan')
            ->orderBy('nama_desa');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_desa', 'like', "%{$search}%")
                  ->orWhere('kecamatan', 'like', "%{$search}%");
            });
        }

        if ($request->has('limit')) {
            $query->limit((int) $request->query('limit'));
        }

        $desa = $query->get();

        return response()->json([
            'message' => 'Daftar desa simple berhasil diambil',
            'data'    => $desa,
        ], 200);
    }

    public function store(StoreDesaRequest $request): JsonResponse
    {
        $desa = Desa::create($request->validated());

        return response()->json([
            'message' => 'Data desa berhasil ditambahkan',
            'data'    => new DesaResource($desa),
        ], 201);
    }

    public function show(Desa $desa): JsonResponse
    {
        $this->authorize('view', $desa);

        return response()->json([
            'message' => 'Data desa berhasil diambil',
            'data'    => new DesaResource($desa),
        ], 200);
    }

    public function update(UpdateDesaRequest $request, Desa $desa): JsonResponse
    {
        $desa->update($request->validated());

        return response()->json([
            'message' => 'Data desa berhasil diperbarui',
            'data'    => new DesaResource($desa),
        ], 200);
    }

    public function destroy(Desa $desa): JsonResponse
    {
        $this->authorize('delete', $desa);

        $desa->delete();

        return response()->json([
            'message' => 'Data desa berhasil dihapus',
        ], 200);
    }
}
