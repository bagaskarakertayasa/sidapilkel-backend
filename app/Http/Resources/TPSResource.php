<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TPSResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'desa_id'           => $this->desa_id,
            'nama_desa'         => $this->desa?->nama_desa,
            'kecamatan'         => $this->desa?->kecamatan,
            'desa'              => $this->desa ? [
                'id'        => $this->desa->id,
                'nama_desa' => $this->desa->nama_desa,
                'kecamatan' => $this->desa->kecamatan,
            ] : null,
            'no_tps'            => $this->no_tps,
            'banjar_tps'        => $this->banjar_tps,
            'jml_pml_tetap'     => $this->jml_pml_tetap,
            'mgn_hak_suara'     => $this->mgn_hak_suara,
            'tdk_mgn_hak_suara' => $this->tdk_mgn_hak_suara,
            'suara_tdk_sah'     => $this->suara_tdk_sah,
            'suara_sah'         => $this->suara_sah,
            'calon_votes'       => TPSCalonVoteResource::collection(
                $this->relationLoaded('calonVotes')
                    ? $this->calonVotes->sortBy(fn($v) => (int) ($v->calon?->no_urut ?? $v->no_urut ?? 999999))->values()
                    : []
            ),
            'created_at'        => $this->created_at?->toISOString(),
            'updated_at'        => $this->updated_at?->toISOString(),
        ];
    }
}
