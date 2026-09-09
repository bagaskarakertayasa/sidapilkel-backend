<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'nama_depan'    => $this->nama_depan,
            'nama_belakang' => $this->nama_belakang,
            'username'      => $this->username,
            'email'         => $this->email,
            'role'          => $this->role,
            'status'        => $this->status,
            'desa_id'       => $this->desa_id,
            'nama_desa'     => $this->desa?->nama_desa,
            'desa'          => $this->desa ? [
                'id'        => $this->desa->id,
                'nama_desa' => $this->desa->nama_desa,
                'kecamatan' => $this->desa->kecamatan,
            ] : null,
            'created_at'    => $this->created_at?->toISOString(),
            'updated_at'    => $this->updated_at?->toISOString(),
        ];
    }
}
