<?php

namespace Tests\Feature\Api;

use App\Models\Desa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DesaAndUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_desa_cannot_access_master_desa_crud(): void
    {
        $desa = Desa::factory()->create();
        $adminDesa = User::factory()->create([
            'role'    => 'ADMIN_DESA',
            'desa_id' => $desa->id,
            'status'  => 'aktif',
        ]);

        Sanctum::actingAs($adminDesa);

        // Index /desa restricted to ADMIN_PUSAT
        $response = $this->getJson('/api/v1/desa');
        $response->assertStatus(403);

        // But /desa/simple is accessible to all authenticated users
        $simpleResponse = $this->getJson('/api/v1/desa/simple');
        $simpleResponse->assertStatus(200);
    }

    public function test_admin_pusat_can_manage_desa(): void
    {
        $adminPusat = User::factory()->create([
            'role'   => 'ADMIN_PUSAT',
            'status' => 'aktif',
        ]);

        Sanctum::actingAs($adminPusat);

        $response = $this->postJson('/api/v1/desa', [
            'nama_desa' => 'Desa Beraban',
            'kecamatan' => 'Kediri',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.nama_desa', 'Desa Beraban');
    }

    public function test_admin_pusat_cannot_deactivate_self(): void
    {
        $adminPusat = User::factory()->create([
            'role'   => 'ADMIN_PUSAT',
            'status' => 'aktif',
        ]);

        Sanctum::actingAs($adminPusat);

        $response = $this->patchJson("/api/v1/users/{$adminPusat->id}/status", [
            'status' => 'nonaktif',
        ]);

        $response->assertStatus(422);
    }
}
