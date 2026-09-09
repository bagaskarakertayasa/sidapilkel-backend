<?php

namespace Tests\Feature\Api;

use App\Models\Calon;
use App\Models\Desa;
use App\Models\TPS;
use App\Models\TPSCalonVote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RekapAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_rekap_and_winner_calculation(): void
    {
        $desa = Desa::factory()->create();
        $calon1 = Calon::factory()->create(['desa_id' => $desa->id, 'no_urut' => 1]);
        $calon2 = Calon::factory()->create(['desa_id' => $desa->id, 'no_urut' => 2]);

        $tps = TPS::factory()->create([
            'desa_id'           => $desa->id,
            'jml_pml_tetap'     => 1000,
            'mgn_hak_suara'     => 800,
            'tdk_mgn_hak_suara' => 200,
            'suara_tdk_sah'     => 50,
            'suara_sah'         => 750,
        ]);

        TPSCalonVote::create([
            'tps_id'       => $tps->id,
            'calon_id'     => $calon1->id,
            'jumlah_suara' => 500,
        ]);

        TPSCalonVote::create([
            'tps_id'       => $tps->id,
            'calon_id'     => $calon2->id,
            'jumlah_suara' => 250,
        ]);

        $admin = User::factory()->create(['role' => 'ADMIN_PUSAT', 'status' => 'aktif']);
        Sanctum::actingAs($admin);

        $response = $this->getJson("/api/v1/rekap/{$desa->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.pemenang.id', $calon1->id)
            ->assertJsonPath('data.pemenang.is_winner', true)
            ->assertJsonPath('data.statistik.total_suara_sah', 750)
            ->assertJsonPath('data.statistik.persentase_hadir', 80);

        // Dashboard stats with pagination
        $dashResponse = $this->getJson('/api/v1/dashboard/stats?page=1&limit=5');
        $dashResponse->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'agregat',
                    'ringkasan_desa' => [
                        'items',
                        'pagination' => [
                            'current_page',
                            'total_pages',
                            'total_records',
                            'limit',
                        ],
                    ],
                ],
            ]);

        // Rekap all desas with pagination
        $allRekapResponse = $this->getJson('/api/v1/rekap?page=1&limit=5');
        $allRekapResponse->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'agregat',
                    'desa' => [
                        'items',
                        'pagination' => [
                            'current_page',
                            'total_pages',
                            'total_records',
                        ],
                    ],
                ],
            ]);
    }
}
