<?php

namespace App\Http\Requests\TPS;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTPSRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user || !$user->isAktif()) {
            return false;
        }
        $tps = $this->route('tps') ?? $this->route('tp');
        if ($user->role === 'ADMIN_DESA') {
            return $tps && (string) $user->desa_id === (string) $tps->desa_id;
        }
        return $user->role === 'ADMIN_PUSAT';
    }

    public function rules(): array
    {
        return [
            'no_tps'                     => 'sometimes|required|integer|min:1',
            'banjar_tps'                 => 'sometimes|required|string|min:2|max:100',
            'jml_pml_tetap'              => 'sometimes|required|integer|min:0',
            'mgn_hak_suara'              => 'nullable|integer|min:0',
            'tdk_mgn_hak_suara'          => 'nullable|integer|min:0',
            'suara_tdk_sah'              => 'sometimes|required|integer|min:0',
            'calon_votes'                => 'nullable|array',
            'calon_votes.*.calon_id'     => 'required_with:calon_votes|exists:calon,id',
            'calon_votes.*.jumlah_suara' => 'required_with:calon_votes|integer|min:0',
        ];
    }
}
