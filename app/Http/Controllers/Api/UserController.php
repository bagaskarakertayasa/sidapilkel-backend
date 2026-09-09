<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\ToggleStatusRequest;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $limit = (int) $request->query('limit', 10);
        $search = $request->query('search');

        $query = User::with('desa');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_depan', 'like', "%{$search}%")
                  ->orWhere('nama_belakang', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('id', 'desc')->paginate($limit);

        return response()->json([
            'message' => 'Data pengguna berhasil diambil',
            'data'    => [
                'items'      => UserResource::collection($users->items()),
                'pagination' => [
                    'current_page'  => $users->currentPage(),
                    'total_pages'   => $users->lastPage(),
                    'total_records' => $users->total(),
                    'limit'         => $users->perPage(),
                    'has_next'      => $users->hasMorePages(),
                    'has_prev'      => $users->currentPage() > 1,
                ],
            ],
        ], 200);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json([
            'message' => 'Pengguna berhasil ditambahkan',
            'data'    => new UserResource($user->load('desa')),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return response()->json([
            'message' => 'Data pengguna berhasil diambil',
            'data'    => new UserResource($user->load('desa')),
        ], 200);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $data = $request->validated();
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Data pengguna berhasil diperbarui',
            'data'    => new UserResource($user->fresh()->load('desa')),
        ], 200);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'Admin Pusat dilarang menghapus akun sendiri',
                'errors'  => ['Self deletion forbidden'],
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'Pengguna berhasil dihapus',
        ], 200);
    }

    public function updatePassword(UpdatePasswordRequest $request, string|int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $this->authorize('updatePassword', $user);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Password pengguna berhasil diperbarui',
        ], 200);
    }

    public function toggleStatus(ToggleStatusRequest $request, string|int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $this->authorize('toggleStatus', $user);

        if ($user->id === $request->user()->id && $request->status === 'nonaktif') {
            return response()->json([
                'message' => 'Admin Pusat dilarang menonaktifkan akun sendiri',
                'errors'  => ['Self deactivation forbidden'],
            ], 422);
        }

        $user->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Status pengguna berhasil diperbarui',
            'data'    => new UserResource($user->load('desa')),
        ], 200);
    }
}
