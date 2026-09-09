<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TPSCalonVoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'tps_id'       => $this->tps_id,
            'calon_id'     => $this->calon_id,
            'no_urut'      => $this->calon?->no_urut,
            'nama_calon'   => $this->calon?->nama_calon,
            'calon'        => $this->calon ? [
                'id'          => $this->calon->id,
                'no_urut'     => $this->calon->no_urut,
                'nama_calon'  => $this->calon->nama_calon,
                'asal_banjar' => $this->calon->asal_banjar,
            ] : null,
            'jumlah_suara' => $this->jumlah_suara,
        ];
    }
}
