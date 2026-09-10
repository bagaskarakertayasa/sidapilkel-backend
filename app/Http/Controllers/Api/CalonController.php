<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Calon\BatchCalonRequest;
use App\Http\Requests\Calon\StoreCalonRequest;
use App\Http\Requests\Calon\UpdateCalonRequest;
use App\Http\Requests\Calon\UploadPhotoRequest;
use App\Http\Resources\CalonResource;
use App\Models\Calon;
use App\Models\Desa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CalonController extends Controller
{
    /**
     * Get candidate list by desa ID.
     */
    public function byDesa(Request $request, string|int $desa_id): JsonResponse
    {
        $user = $request->user();
        if ($user->role === 'ADMIN_DESA' && (string) $user->desa_id !== (string) $desa_id) {
            return response()->json([
                'message' => 'Akses ditolak: Anda tidak memiliki izin untuk mengakses data desa lain.',
                'errors'  => ['Forbidden'],
            ], 403);
        }

        $calonList = Calon::with('desa')
            ->where('desa_id', $desa_id)
            ->orderBy('no_urut')
            ->get();

        return response()->json([
            'message' => 'Data calon berhasil diambil',
            'data'    => CalonResource::collection($calonList),
        ], 200);
    }

    public function store(StoreCalonRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $maxNoUrut = Calon::where('desa_id', $validated['desa_id'])->max('no_urut') ?? 0;
        $validated['no_urut'] = $maxNoUrut + 1;
        
        $calon = Calon::create($validated);

        return response()->json([
            'message' => 'Data calon berhasil ditambahkan',
            'data'    => new CalonResource($calon->load('desa')),
        ], 201);
    }

    public function update(UpdateCalonRequest $request, Calon $calon): JsonResponse
    {
        $this->authorize('update', $calon);

        $calon->update($request->validated());

        return response()->json([
            'message' => 'Data calon berhasil diperbarui',
            'data'    => new CalonResource($calon->load('desa')),
        ], 200);
    }

    public function destroy(Calon $calon): JsonResponse
    {
        $this->authorize('delete', $calon);

        if ($calon->foto && \Illuminate\Support\Facades\Storage::disk('public')->exists($calon->foto)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($calon->foto);
        }

        $calon->delete();

        return response()->json([
            'message' => 'Data calon berhasil dihapus',
        ], 200);
    }

    /**
     * Upload photo of candidate.
     */
    public function uploadPhoto(UploadPhotoRequest $request): JsonResponse
    {
        $file = $request->file('foto');
        $path = $file->store('calon_photos', 'public');
        $url = url(Storage::url($path));

        return response()->json([
            'message' => 'Foto calon berhasil diunggah',
            'data'    => [
                'foto' => $path,
                'url'  => $url,
            ],
        ], 200);
    }

    /**
     * Reset/Delete all candidates for a village.
     */
    public function resetByDesa(Request $request, string|int $desa_id): JsonResponse
    {
        $user = $request->user();
        if ($user->role === 'ADMIN_DESA' && (string) $user->desa_id !== (string) $desa_id) {
            return response()->json([
                'message' => 'Akses ditolak: Anda tidak memiliki izin untuk mereset data desa lain.',
                'errors'  => ['Forbidden'],
            ], 403);
        }

        \App\Models\TPS::where('desa_id', $desa_id)->delete();
        
        $calons = Calon::where('desa_id', $desa_id)->get();
        foreach ($calons as $calon) {
            if ($calon->foto && \Illuminate\Support\Facades\Storage::disk('public')->exists($calon->foto)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($calon->foto);
            }
        }
        
        Calon::where('desa_id', $desa_id)->delete();

        return response()->json([
            'message' => 'Data calon dan TPS untuk desa tersebut berhasil direset',
        ], 200);
    }
}
