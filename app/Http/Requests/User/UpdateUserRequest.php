<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdminPusat() ?? false;
    }

    public function rules(): array
    {
        $userId = $this->route('user') instanceof \App\Models\User
            ? $this->route('user')->id
            : $this->route('user') ?? $this->route('id');

        return [
            'nama_depan'    => 'sometimes|required|string|max:100',
            'nama_belakang' => 'nullable|string|max:100',
            'username'      => 'sometimes|required|string|min:4|max:100|unique:users,username,' . $userId,
            'email'         => 'sometimes|required|email|max:150|unique:users,email,' . $userId,
            'role'          => 'sometimes|required|in:ADMIN_PUSAT,ADMIN_DESA',
            'status'        => 'sometimes|required|in:aktif,nonaktif',
            'desa_id'       => 'nullable|exists:desa,id',
        ];
    }
}
