<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CalonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $fotoUrl = null;
        if ($this->foto) {
            $fotoUrl = str_starts_with($this->foto, 'http')
                ? $this->foto
                : url(Storage::url($this->foto));
        }

        return [
            'id'          => $this->id,
            'desa_id'     => $this->desa_id,
            'nama_desa'   => $this->desa?->nama_desa,
            'no_urut'     => $this->no_urut,
            'nama_calon'  => $this->nama_calon,
            'foto'        => $this->foto,
            'foto_url'    => $fotoUrl,
            'asal_banjar' => $this->asal_banjar,
            'created_at'  => $this->created_at?->toISOString(),
            'updated_at'  => $this->updated_at?->toISOString(),
        ];
    }
}
