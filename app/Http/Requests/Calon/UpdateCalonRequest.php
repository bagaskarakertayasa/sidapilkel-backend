<?php

namespace App\Http\Requests\Calon;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCalonRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user || !$user->isAktif()) {
            return false;
        }

        $calon = $this->route('calon');
        if ($user->role === 'ADMIN_DESA') {
            return $calon && (string) $user->desa_id === (string) $calon->desa_id;
        }

        return $user->role === 'ADMIN_PUSAT';
    }

    public function rules(): array
    {
        return [
            'no_urut'     => 'sometimes|required|integer|min:1',
            'nama_calon'  => 'sometimes|required|string|max:150',
            'foto'        => 'nullable|string|max:255',
            'asal_banjar' => 'nullable|string|max:150',
        ];
    }
}
