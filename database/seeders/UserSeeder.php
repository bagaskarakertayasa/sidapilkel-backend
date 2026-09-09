<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Clears only the users table without affecting other tables.
     */
    public function run(): void
    {
        // 1. Delete all existing users cleanly
        Schema::disableForeignKeyConstraints();
        User::query()->forceDelete();
        Schema::enableForeignKeyConstraints();

        // 2. Seed Admin Pusat (kredensial: admin / admin123)
        User::create([
            'nama_depan'    => 'Admin',
            'nama_belakang' => 'Pusat',
            'username'      => 'admin',
            'email'         => 'admin@tabanan.go.id',
            'password'      => Hash::make('admin123'),
            'role'          => 'ADMIN_PUSAT',
            'status'        => 'aktif',
            'desa_id'       => null,
        ]);

        // 3. Seed Admin Desa untuk desa-desa yang ada
        $desas = Desa::all();

        if ($desas->isEmpty()) {
            $desa1 = Desa::create(['nama_desa' => 'Perean Kangin', 'kecamatan' => 'Baturiti']);
            $desa2 = Desa::create(['nama_desa' => 'Biaung', 'kecamatan' => 'Penebel']);
            $desas = collect([$desa1, $desa2]);
        }

        foreach ($desas as $desa) {
            $slugUsername = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($desa->nama_desa)));
            $slugUsername = trim($slugUsername, '_');

            User::create([
                'nama_depan'    => 'Admin',
                'nama_belakang' => 'Desa ' . $desa->nama_desa,
                'username'      => $slugUsername,
                'email'         => $slugUsername . '@tabanan.go.id',
                'password'      => Hash::make('password123'),
                'role'          => 'ADMIN_DESA',
                'status'        => 'aktif',
                'desa_id'       => $desa->id,
            ]);
        }

        // Pastikan admindesa1 juga tersedia jika belum ada
        if (!User::where('username', 'admindesa1')->exists() && $desas->isNotEmpty()) {
            User::create([
                'nama_depan'    => 'Admin',
                'nama_belakang' => 'Desa 1',
                'username'      => 'admindesa1',
                'email'         => 'admindesa1@tabanan.go.id',
                'password'      => Hash::make('password123'),
                'role'          => 'ADMIN_DESA',
                'status'        => 'aktif',
                'desa_id'       => $desas->first()->id,
            ]);
        }
    }
}
