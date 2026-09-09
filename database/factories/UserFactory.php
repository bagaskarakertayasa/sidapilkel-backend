<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'nama_depan'    => fake()->firstName(),
            'nama_belakang' => fake()->lastName(),
            'username'      => fake()->unique()->userName(),
            'email'         => fake()->unique()->safeEmail(),
            'password'      => static::$password ??= Hash::make('password'),
            'role'          => 'ADMIN_DESA',
            'status'        => 'aktif',
            'desa_id'       => null,
        ];
    }

    public function adminPusat(): static
    {
        return $this->state(fn () => [
            'role'    => 'ADMIN_PUSAT',
            'desa_id' => null,
        ]);
    }

    public function adminDesa(int $desaId): static
    {
        return $this->state(fn () => [
            'role'    => 'ADMIN_DESA',
            'desa_id' => $desaId,
        ]);
    }

    public function nonaktif(): static
    {
        return $this->state(fn () => [
            'status' => 'nonaktif',
        ]);
    }
}
