<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdminPusat() ?? false;
    }

    public function rules(): array
    {
        return [
            'nama_depan'    => 'required|string|max:100',
            'nama_belakang' => 'nullable|string|max:100',
            'username'      => 'required|string|min:4|max:100|unique:users,username',
            'email'         => 'required|email|max:150|unique:users,email',
            'password'      => 'required|string|min:6',
            'role'          => 'required|in:ADMIN_PUSAT,ADMIN_DESA',
            'status'        => 'nullable|in:aktif,nonaktif',
            'desa_id'       => 'nullable|required_if:role,ADMIN_DESA|exists:desa,id',
        ];
    }
}
