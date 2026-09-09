<?php

namespace Tests\Feature\Api;

use App\Models\Calon;
use App\Models\Desa;
use App\Models\TPS;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TPSTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_desa_cannot_create_tps_if_calon_is_empty(): void
    {
        $desa = Desa::factory()->create();
        $adminDesa = User::factory()->create([
            'role'    => 'ADMIN_DESA',
            'desa_id' => $desa->id,
            'status'  => 'aktif',
        ]);

        Sanctum::actingAs($adminDesa);

        $response = $this->postJson('/api/v1/tps', [
            'desa_id'       => $desa->id,
            'no_tps'        => 1,
            'banjar_tps'    => 'Banjar Tengah',
            'jml_pml_tetap' => 500,
            'suara_tdk_sah' => 5,
            'calon_votes'   => [],
        ]);

        $response->assertStatus(422);
    }

    public function test_admin_desa_cannot_modify_tps_belonging_to_another_village(): void
    {
        $desaA = Desa::factory()->create();
        $desaB = Desa::factory()->create();

        $adminDesaA = User::factory()->create([
            'role'    => 'ADMIN_DESA',
            'desa_id' => $desaA->id,
            'status'  => 'aktif',
        ]);

        $tpsDesaB = TPS::factory()->create(['desa_id' => $desaB->id]);

        Sanctum::actingAs($adminDesaA);

        $response = $this->deleteJson("/api/v1/tps/{$tpsDesaB->id}");

        $response->assertStatus(403);
    }

    public function test_vote_math_and_suara_sah_are_accurately_calculated(): void
    {
        $desa = Desa::factory()->create();
        $calon1 = Calon::factory()->create(['desa_id' => $desa->id, 'no_urut' => 1]);
        $calon2 = Calon::factory()->create(['desa_id' => $desa->id, 'no_urut' => 2]);

        $admin = User::factory()->create(['role' => 'ADMIN_PUSAT', 'status' => 'aktif']);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/tps', [
            'desa_id'       => $desa->id,
            'no_tps'        => 1,
            'banjar_tps'    => 'Banjar Kaja',
            'jml_pml_tetap' => 600,
            'suara_tdk_sah' => 10,
            'calon_votes'   => [
                ['calon_id' => $calon1->id, 'jumlah_suara' => 300],
                ['calon_id' => $calon2->id, 'jumlah_suara' => 190],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.suara_sah', 490)
            ->assertJsonPath('data.mgn_hak_suara', 500)
            ->assertJsonPath('data.tdk_mgn_hak_suara', 100);
    }

    public function test_tps_index_includes_desa_object_and_sorted_calon_votes_ascending(): void
    {
        $desa = Desa::factory()->create(['nama_desa' => 'Desa Megati']);
        // Insert candidates out of order
        $calon3 = Calon::factory()->create(['desa_id' => $desa->id, 'no_urut' => 3]);
        $calon1 = Calon::factory()->create(['desa_id' => $desa->id, 'no_urut' => 1]);
        $calon2 = Calon::factory()->create(['desa_id' => $desa->id, 'no_urut' => 2]);

        $admin = User::factory()->create(['role' => 'ADMIN_PUSAT', 'status' => 'aktif']);
        Sanctum::actingAs($admin);

        // Create TPS with votes in scrambled order
        $this->postJson('/api/v1/tps', [
            'desa_id'       => $desa->id,
            'no_tps'        => 1,
            'banjar_tps'    => 'Banjar Dauh',
            'jml_pml_tetap' => 600,
            'suara_tdk_sah' => 10,
            'calon_votes'   => [
                ['calon_id' => $calon3->id, 'jumlah_suara' => 100],
                ['calon_id' => $calon1->id, 'jumlah_suara' => 250],
                ['calon_id' => $calon2->id, 'jumlah_suara' => 150],
            ],
        ])->assertStatus(201);

        $indexRes = $this->getJson('/api/v1/tps');
        $indexRes->assertStatus(200);

        $item = $indexRes->json('data.items.0');
        $this->assertNotNull($item);
        $this->assertEquals('Desa Megati', $item['nama_desa']);
        $this->assertEquals('Desa Megati', $item['desa']['nama_desa']);

        // Check ascending order of candidate votes
        $votes = $item['calon_votes'];
        $this->assertCount(3, $votes);
        $this->assertEquals(1, $votes[0]['no_urut']);
        $this->assertEquals(2, $votes[1]['no_urut']);
        $this->assertEquals(3, $votes[2]['no_urut']);
    }
}
