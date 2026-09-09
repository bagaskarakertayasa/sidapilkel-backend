<?php

namespace Database\Factories;

use App\Models\Desa;
use App\Models\TPS;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TPS>
 */
class TPSFactory extends Factory
{
    protected $model = TPS::class;

    public function definition(): array
    {
        return [
            'desa_id'           => Desa::factory(),
            'no_tps'            => fake()->numberBetween(1, 20),
            'banjar_tps'        => 'Banjar ' . fake()->streetName(),
            'jml_pml_tetap'     => 500,
            'mgn_hak_suara'     => 450,
            'tdk_mgn_hak_suara' => 50,
            'suara_tdk_sah'     => 10,
            'suara_sah'         => 440,
        ];
    }
}
