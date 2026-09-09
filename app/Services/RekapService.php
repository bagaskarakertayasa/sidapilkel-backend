<?php

namespace App\Services;

use App\Models\Calon;
use App\Models\Desa;
use App\Models\TPS;
use App\Models\TPSCalonVote;
use Illuminate\Support\Facades\Cache;

class RekapService
{
    /**
     * Get recap for a single desa.
     */
    public function getDesaRecap(Desa $desa): array
    {
        $tpsList = TPS::where('desa_id', $desa->id)->get();
        $calonList = Calon::where('desa_id', $desa->id)->orderBy('no_urut')->get();

        $totalTps = $tpsList->count();
        $totalDpt = (int) $tpsList->sum('jml_pml_tetap');
        $totalHadir = (int) $tpsList->sum('mgn_hak_suara');
        $totalTidakHadir = (int) $tpsList->sum('tdk_mgn_hak_suara');
        $totalSuaraSah = (int) $tpsList->sum('suara_sah');
        $totalSuaraTdkSah = (int) $tpsList->sum('suara_tdk_sah');
        $persentaseHadir = $totalDpt > 0 ? round(($totalHadir / $totalDpt) * 100, 2) : 0;

        // Fetch vote counts per candidate
        $calonIds = $calonList->pluck('id')->all();
        $voteCounts = TPSCalonVote::whereIn('calon_id', $calonIds)
            ->selectRaw('calon_id, SUM(jumlah_suara) as total_suara')
            ->groupBy('calon_id')
            ->pluck('total_suara', 'calon_id');

        $maxVotes = -1;
        $winnerId = null;

        $calonRecap = [];
        foreach ($calonList as $calon) {
            $suara = (int) ($voteCounts[$calon->id] ?? 0);
            if ($suara > $maxVotes && $suara > 0) {
                $maxVotes = $suara;
                $winnerId = $calon->id;
            }

            $persentase = $totalSuaraSah > 0 ? round(($suara / $totalSuaraSah) * 100, 2) : 0;

            $calonRecap[] = [
                'id'          => $calon->id,
                'no_urut'     => $calon->no_urut,
                'nama_calon'  => $calon->nama_calon,
                'foto'        => $calon->foto,
                'asal_banjar' => $calon->asal_banjar,
                'total_suara' => $suara,
                'persentase'  => $persentase,
                'is_winner'   => false, // set below
            ];
        }

        $pemenang = null;
        if ($winnerId !== null) {
            foreach ($calonRecap as &$item) {
                if ($item['id'] === $winnerId) {
                    $item['is_winner'] = true;
                    $pemenang = $item;
                    break;
                }
            }
            unset($item);
        }

        return [
            'desa' => [
                'id'        => $desa->id,
                'nama_desa' => $desa->nama_desa,
                'kecamatan' => $desa->kecamatan,
            ],
            'statistik' => [
                'total_tps'             => $totalTps,
                'total_dpt'             => $totalDpt,
                'total_hadir'           => $totalHadir,
                'total_tidak_hadir'     => $totalTidakHadir,
                'total_suara_sah'       => $totalSuaraSah,
                'total_suara_tidak_sah' => $totalSuaraTdkSah,
                'persentase_hadir'      => $persentaseHadir,
            ],
            'pemenang' => $pemenang,
            'calon'    => $calonRecap,
        ];
    }

    /**
     * Calculate recap for a single Desa model instance using eager loaded relations.
     */
    public function calculateDesaRecapFromModel(Desa $desa): array
    {
        $totalTps = $desa->tps->count();
        $totalDpt = (int) $desa->tps->sum('jml_pml_tetap');
        $totalHadir = (int) $desa->tps->sum('mgn_hak_suara');
        $totalTidakHadir = (int) $desa->tps->sum('tdk_mgn_hak_suara');
        $totalSuaraSah = (int) $desa->tps->sum('suara_sah');
        $totalSuaraTdkSah = (int) $desa->tps->sum('suara_tdk_sah');
        $persentaseHadir = $totalDpt > 0 ? round(($totalHadir / $totalDpt) * 100, 2) : 0;

        $maxVotes = -1;
        $winnerId = null;

        $calonRecap = [];
        foreach ($desa->calon as $calon) {
            $suara = (int) $calon->votes->sum('jumlah_suara');
            $persentase = $totalSuaraSah > 0 ? round(($suara / $totalSuaraSah) * 100, 2) : 0;

            if ($suara > $maxVotes && $suara > 0) {
                $maxVotes = $suara;
                $winnerId = $calon->id;
            }

            $calonRecap[] = [
                'id'          => $calon->id,
                'no_urut'     => $calon->no_urut,
                'nama_calon'  => $calon->nama_calon,
                'foto'        => $calon->foto,
                'asal_banjar' => $calon->asal_banjar,
                'total_suara' => $suara,
                'persentase'  => $persentase,
                'is_winner'   => false,
            ];
        }

        $pemenang = null;
        if ($winnerId !== null) {
            foreach ($calonRecap as &$item) {
                if ($item['id'] === $winnerId) {
                    $item['is_winner'] = true;
                    $pemenang = $item;
                    break;
                }
            }
            unset($item);
        }

        return [
            'desa' => [
                'id'        => $desa->id,
                'nama_desa' => $desa->nama_desa,
                'kecamatan' => $desa->kecamatan,
            ],
            'statistik' => [
                'total_tps'             => $totalTps,
                'total_dpt'             => $totalDpt,
                'total_hadir'           => $totalHadir,
                'total_tidak_hadir'     => $totalTidakHadir,
                'total_suara_sah'       => $totalSuaraSah,
                'total_suara_tidak_sah' => $totalSuaraTdkSah,
                'persentase_hadir'      => $persentaseHadir,
            ],
            'pemenang' => $pemenang,
            'calon'    => $calonRecap,
        ];
    }

    /**
     * Get recap for all villages with optional search and pagination using eager loading.
     */
    public function getAllDesaRecap(?string $search = null, ?int $page = null, int $limit = 10): array
    {
        $totalDesa = Desa::count();
        $totalTps = TPS::count();
        $totalDpt = (int) TPS::sum('jml_pml_tetap');
        $totalHadir = (int) TPS::sum('mgn_hak_suara');
        $totalTidakHadir = (int) TPS::sum('tdk_mgn_hak_suara');
        $totalSuaraSah = (int) TPS::sum('suara_sah');
        $totalSuaraTdkSah = (int) TPS::sum('suara_tdk_sah');
        $persentaseHadir = $totalDpt > 0 ? round(($totalHadir / $totalDpt) * 100, 2) : 0;

        $aggregate = [
            'total_desa'            => $totalDesa,
            'total_tps'             => $totalTps,
            'total_dpt'             => $totalDpt,
            'total_hadir'           => $totalHadir,
            'total_tidak_hadir'     => $totalTidakHadir,
            'total_suara_sah'       => $totalSuaraSah,
            'total_suara_tidak_sah' => $totalSuaraTdkSah,
            'persentase_hadir'      => $persentaseHadir,
        ];

        $query = Desa::with(['tps', 'calon.votes']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_desa', 'like', "%{$search}%")
                  ->orWhere('kecamatan', 'like', "%{$search}%");
            });
        }

        $query->orderBy('kecamatan')->orderBy('nama_desa');

        if ($page !== null) {
            $paginator = $query->paginate($limit, ['*'], 'page', $page);
            $items = array_map(fn($d) => $this->calculateDesaRecapFromModel($d), $paginator->items());

            return [
                'agregat' => $aggregate,
                'desa'    => [
                    'items'      => $items,
                    'pagination' => [
                        'current_page'  => $paginator->currentPage(),
                        'total_pages'   => $paginator->lastPage(),
                        'total_records' => $paginator->total(),
                        'limit'         => $paginator->perPage(),
                        'has_next'      => $paginator->hasMorePages(),
                        'has_prev'      => $paginator->currentPage() > 1,
                    ],
                ],
            ];
        }

        $desaList = $query->get();
        $items = array_map(fn($d) => $this->calculateDesaRecapFromModel($d), $desaList->all());

        return [
            'agregat' => $aggregate,
            'desa'    => $items,
        ];
    }

    /**
     * Get dashboard summary statistics based on user role.
     */
    public function getDashboardStats($user, $request = null): array
    {
        if ($user->role === 'ADMIN_DESA') {
            $desa = Desa::find($user->desa_id);
            if (!$desa) {
                return ['message' => 'Desa tidak ditemukan'];
            }
            return $this->getDesaRecap($desa);
        }

        // ADMIN_PUSAT
        $page = $request?->has('page') ? (int) $request->query('page', 1) : 1;
        $limit = (int) ($request?->query('limit', 10) ?? 10);
        $search = $request?->query('search');

        $all = $this->getAllDesaRecap($search, $page, $limit);
        $items = array_map(function ($d) {
            return [
                'desa_id'          => $d['desa']['id'],
                'nama_desa'        => $d['desa']['nama_desa'],
                'kecamatan'        => $d['desa']['kecamatan'],
                'total_tps'        => $d['statistik']['total_tps'],
                'total_dpt'        => $d['statistik']['total_dpt'],
                'persentase_hadir' => $d['statistik']['persentase_hadir'],
                'pemenang'         => $d['pemenang']['nama_calon'] ?? null,
            ];
        }, $all['desa']['items']);

        return [
            'agregat'        => $all['agregat'],
            'ringkasan_desa' => [
                'items'      => $items,
                'pagination' => $all['desa']['pagination'],
            ],
        ];
    }
}
