<?php

namespace App\Services;

use App\Models\Calon;
use App\Models\TPS;
use App\Models\TPSCalonVote;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TPSService
{
    /**
     * Get paginated TPS with optional search and desa filter.
     */
    public function getPaginatedTPS(User $user, int $limit = 10, ?string $search = null, string|int|null $desaId = null): LengthAwarePaginator
    {
        $query = TPS::with(['desa', 'calonVotes.calon']);

        if ($user->role === 'ADMIN_DESA') {
            $query->where('desa_id', $user->desa_id);
        } elseif ($desaId) {
            $query->where('desa_id', $desaId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('banjar_tps', 'like', "%{$search}%")
                  ->orWhere('no_tps', 'like', "%{$search}%")
                  ->orWhereHas('desa', function ($desaQuery) use ($search) {
                      $desaQuery->where('nama_desa', 'like', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('desa_id')->orderBy('no_tps')->paginate($limit);
    }

    /**
     * Create a new TPS and related candidate votes.
     */
    public function createTPS(array $data): TPS
    {
        $desaId = $data['desa_id'];

        $calonCount = Calon::where('desa_id', $desaId)->count();
        if ($calonCount === 0) {
            throw ValidationException::withMessages([
                'desa_id' => ['Data calon perbekel untuk desa ini masih kosong. Harap isi data calon perbekel terlebih dahulu'],
            ]);
        }

        $calonVotes = $data['calon_votes'] ?? [];
        $suaraSah = array_reduce($calonVotes, fn($carry, $item) => $carry + (int)($item['jumlah_suara'] ?? 0), 0);
        $suaraTdkSah = (int)($data['suara_tdk_sah'] ?? 0);
        $jmlPmlTetap = (int)($data['jml_pml_tetap'] ?? 0);

        $mgnHakSuara = (int)($data['mgn_hak_suara'] ?? 0);
        if ($mgnHakSuara <= 0) {
            $mgnHakSuara = $suaraSah + $suaraTdkSah;
        }

        $tdkMgnHakSuara = (int)($data['tdk_mgn_hak_suara'] ?? 0);
        if ($tdkMgnHakSuara <= 0 && $jmlPmlTetap >= $mgnHakSuara) {
            $tdkMgnHakSuara = $jmlPmlTetap - $mgnHakSuara;
        }

        return DB::transaction(function () use ($data, $suaraSah, $mgnHakSuara, $tdkMgnHakSuara, $calonVotes) {
            $tps = TPS::create([
                'desa_id'           => $data['desa_id'],
                'no_tps'            => $data['no_tps'],
                'banjar_tps'        => $data['banjar_tps'],
                'jml_pml_tetap'     => $data['jml_pml_tetap'],
                'mgn_hak_suara'     => $mgnHakSuara,
                'tdk_mgn_hak_suara' => $tdkMgnHakSuara,
                'suara_tdk_sah'     => $data['suara_tdk_sah'],
                'suara_sah'         => $suaraSah,
            ]);

            foreach ($calonVotes as $vote) {
                TPSCalonVote::create([
                    'tps_id'       => $tps->id,
                    'calon_id'     => $vote['calon_id'],
                    'jumlah_suara' => $vote['jumlah_suara'],
                ]);
            }

            return $tps->load(['desa', 'calonVotes.calon']);
        });
    }

    /**
     * Update an existing TPS.
     */
    public function updateTPS(TPS $tps, array $data): TPS
    {
        return DB::transaction(function () use ($tps, $data) {
            $calonVotes = $data['calon_votes'] ?? null;

            if ($calonVotes !== null) {
                TPSCalonVote::where('tps_id', $tps->id)->delete();
                $suaraSah = 0;
                foreach ($calonVotes as $vote) {
                    TPSCalonVote::create([
                        'tps_id'       => $tps->id,
                        'calon_id'     => $vote['calon_id'],
                        'jumlah_suara' => $vote['jumlah_suara'],
                    ]);
                    $suaraSah += (int) $vote['jumlah_suara'];
                }
                $tps->suara_sah = $suaraSah;
            }

            $suaraTdkSah = array_key_exists('suara_tdk_sah', $data) ? (int)$data['suara_tdk_sah'] : $tps->suara_tdk_sah;
            $jmlPmlTetap = array_key_exists('jml_pml_tetap', $data) ? (int)$data['jml_pml_tetap'] : $tps->jml_pml_tetap;

            if (isset($data['no_tps'])) $tps->no_tps = $data['no_tps'];
            if (isset($data['banjar_tps'])) $tps->banjar_tps = $data['banjar_tps'];
            if (isset($data['jml_pml_tetap'])) $tps->jml_pml_tetap = $jmlPmlTetap;
            if (isset($data['suara_tdk_sah'])) $tps->suara_tdk_sah = $suaraTdkSah;

            $mgnHakSuara = isset($data['mgn_hak_suara']) ? (int)$data['mgn_hak_suara'] : $tps->mgn_hak_suara;
            if ($mgnHakSuara <= 0) {
                $mgnHakSuara = $tps->suara_sah + $tps->suara_tdk_sah;
            }
            $tps->mgn_hak_suara = $mgnHakSuara;

            $tdkMgnHakSuara = isset($data['tdk_mgn_hak_suara']) ? (int)$data['tdk_mgn_hak_suara'] : $tps->tdk_mgn_hak_suara;
            if ($tdkMgnHakSuara <= 0 && $tps->jml_pml_tetap >= $tps->mgn_hak_suara) {
                $tdkMgnHakSuara = $tps->jml_pml_tetap - $tps->mgn_hak_suara;
            }
            $tps->tdk_mgn_hak_suara = $tdkMgnHakSuara;

            $tps->save();

            return $tps->load(['desa', 'calonVotes.calon']);
        });
    }

    /**
     * Delete a TPS and its votes.
     */
    public function deleteTPS(TPS $tps): void
    {
        DB::transaction(function () use ($tps) {
            $tps->calonVotes()->delete();
            $tps->delete();
        });
    }
}
