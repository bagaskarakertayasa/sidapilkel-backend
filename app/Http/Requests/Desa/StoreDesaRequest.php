<?php

namespace App\Http\Requests\Desa;

use Illuminate\Foundation\Http\FormRequest;

class StoreDesaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdminPusat() ?? false;
    }

    public function rules(): array
    {
        return [
            'nama_desa' => 'required|string|max:100|unique:desa,nama_desa',
            'kecamatan' => 'required|string|max:100',
        ];
    }
}
