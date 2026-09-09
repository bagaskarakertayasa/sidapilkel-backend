<?php

namespace App\Http\Requests\Desa;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDesaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdminPusat() ?? false;
    }

    public function rules(): array
    {
        $desaId = $this->route('desa') instanceof \App\Models\Desa
            ? $this->route('desa')->id
            : $this->route('desa') ?? $this->route('id');

        return [
            'nama_desa' => 'sometimes|required|string|max:100|unique:desa,nama_desa,' . $desaId,
            'kecamatan' => 'sometimes|required|string|max:100',
        ];
    }
}
