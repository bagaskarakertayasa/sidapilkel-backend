<?php

namespace Database\Factories;

use App\Models\Calon;
use App\Models\Desa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Calon>
 */
class CalonFactory extends Factory
{
    protected $model = Calon::class;

    public function definition(): array
    {
        return [
            'desa_id'     => Desa::factory(),
            'no_urut'     => fake()->numberBetween(1, 5),
            'nama_calon'  => fake()->name(),
            'foto'        => null,
            'asal_banjar' => 'Banjar ' . fake()->streetName(),
        ];
    }
}
