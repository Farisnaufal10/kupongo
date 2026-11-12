<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'name' => fake()->name(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'pengelola',
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * State untuk pengguna role tenant.
     */
    public function tenant(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'tenant',
        ]);
    }

    /**
     * State untuk pengguna role pengelola.
     */
    public function pengelola(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'pengelola',
        ]);
    }

    /**
     * State untuk akun yang berstatus non-aktif.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
