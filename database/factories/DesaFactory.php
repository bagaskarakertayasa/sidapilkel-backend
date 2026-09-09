<?php

namespace Database\Factories;

use App\Models\Desa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Desa>
 */
class DesaFactory extends Factory
{
    protected $model = Desa::class;

    public function definition(): array
    {
        return [
            'nama_desa' => fake()->unique()->city(),
            'kecamatan' => fake()->randomElement(['Tabanan', 'Kediri', 'Marga', 'Baturiti', 'Penebel', 'Kerambitan', 'Selemadeg', 'Pupuan']),
        ];
    }
}
