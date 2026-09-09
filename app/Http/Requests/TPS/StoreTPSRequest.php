<?php

namespace App\Http\Requests\TPS;

use Illuminate\Foundation\Http\FormRequest;

class StoreTPSRequest extends FormRequest
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
            'desa_id'                    => 'required|exists:desa,id',
            'no_tps'                     => 'required|integer|min:1',
            'banjar_tps'                 => 'required|string|min:2|max:100',
            'jml_pml_tetap'              => 'required|integer|min:0',
            'mgn_hak_suara'              => 'nullable|integer|min:0',
            'tdk_mgn_hak_suara'          => 'nullable|integer|min:0',
            'suara_tdk_sah'              => 'required|integer|min:0',
            'calon_votes'                => 'required|array|min:1',
            'calon_votes.*.calon_id'     => 'required|exists:calon,id',
            'calon_votes.*.jumlah_suara' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'desa_id.required'     => 'Desa wajib dipilih.',
            'no_tps.required'      => 'Nomor TPS wajib diisi.',
            'calon_votes.required' => 'Rincian perolehan suara calon wajib diisi.',
        ];
    }
}
