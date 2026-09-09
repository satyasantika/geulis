<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            // Menyerupai NIS: 10 digit, unik.
            'username' => fake()->unique()->numerify('##########'),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'jenis_kelamin' => fake()->randomElement(['L', 'P']),
            'aktif' => true,
            'remember_token' => Str::random(10),
        ];
    }
}
