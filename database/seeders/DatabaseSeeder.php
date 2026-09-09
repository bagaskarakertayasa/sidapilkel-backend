<?php

namespace Database\Seeders;

use App\Models\Calon;
use App\Models\Desa;
use App\Models\TPS;
use App\Models\TPSCalonVote;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin Pusat
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'nama_depan'    => 'Admin',
                'nama_belakang' => 'Pusat',
                'email'         => 'admin@tabanan.go.id',
                'password'      => Hash::make('admin123'),
                'role'          => 'ADMIN_PUSAT',
                'status'        => 'aktif',
                'desa_id'       => null,
            ]
        );

        // 2. Master Desa
        $desa1 = Desa::updateOrCreate(
            ['nama_desa' => 'Dajan Peken'],
            ['kecamatan' => 'Tabanan']
        );

        $desa2 = Desa::updateOrCreate(
            ['nama_desa' => 'Dauh Peken'],
            ['kecamatan' => 'Tabanan']
        );

        // 3. Admin Desa
        User::updateOrCreate(
            ['username' => 'admindesa1'],
            [
                'nama_depan'    => 'Admin',
                'nama_belakang' => 'Dajan Peken',
                'email'         => 'admindesa1@tabanan.go.id',
                'password'      => Hash::make('password123'),
                'role'          => 'ADMIN_DESA',
                'status'        => 'aktif',
                'desa_id'       => $desa1->id,
            ]
        );

        // 4. Sample Calon for Desa 1
        $calon1 = Calon::firstOrCreate(
            ['desa_id' => $desa1->id, 'no_urut' => 1],
            [
                'nama_calon'  => 'I Wayan Sudarma',
                'asal_banjar' => 'Banjar Pande',
            ]
        );

        $calon2 = Calon::firstOrCreate(
            ['desa_id' => $desa1->id, 'no_urut' => 2],
            [
                'nama_calon'  => 'I Made Wijaya',
                'asal_banjar' => 'Banjar Delod Peken',
            ]
        );

        // 5. Sample TPS for Desa 1
        $tps1 = TPS::firstOrCreate(
            ['desa_id' => $desa1->id, 'no_tps' => 1],
            [
                'banjar_tps'        => 'Banjar Pande',
                'jml_pml_tetap'     => 500,
                'mgn_hak_suara'     => 450,
                'tdk_mgn_hak_suara' => 50,
                'suara_tdk_sah'     => 10,
                'suara_sah'         => 440,
            ]
        );

        TPSCalonVote::updateOrCreate(
            ['tps_id' => $tps1->id, 'calon_id' => $calon1->id],
            ['jumlah_suara' => 250]
        );

        TPSCalonVote::updateOrCreate(
            ['tps_id' => $tps1->id, 'calon_id' => $calon2->id],
            ['jumlah_suara' => 190]
        );
    }
}
