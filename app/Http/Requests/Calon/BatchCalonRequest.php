<?php

namespace App\Http\Requests\Calon;

use Illuminate\Foundation\Http\FormRequest;

class BatchCalonRequest extends FormRequest
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
            'desa_id'              => 'required|exists:desa,id',
            'calon'                => 'required|array|min:1',
            'calon.*.no_urut'      => 'required|integer|min:1',
            'calon.*.nama_calon'   => 'required|string|max:150',
            'calon.*.foto'         => 'nullable|string|max:255',
            'calon.*.asal_banjar'  => 'nullable|string|max:150',
        ];
    }
}
