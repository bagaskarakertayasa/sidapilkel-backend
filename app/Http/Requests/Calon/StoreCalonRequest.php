<?php

namespace App\Http\Requests\Calon;

use Illuminate\Foundation\Http\FormRequest;

class StoreCalonRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user || !$user->isAktif()) {
            return false;
        }

        if ($user->role === 'ADMIN_DESA') {
            return (string) $user->desa_id === (string) $this->desa_id;
        }

        return $user->role === 'ADMIN_PUSAT';
    }

    public function rules(): array
    {
        return [
            'desa_id'     => 'required|exists:desa,id',
            'nama_calon'  => 'required|string|max:150',
            'foto'        => 'nullable|string|max:255',
            'asal_banjar' => 'nullable|string|max:150',
        ];
    }
}
