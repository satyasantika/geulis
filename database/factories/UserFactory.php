<?php

namespace Database\Factories;

use App\Enums\Peran;
use App\Models\Role;
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

    /**
     * PIN 6 digit siswa; di-hash ke kolom `password` seperti di produksi.
     */
    public function pin(string $pin): static
    {
        return $this->state(fn (): array => ['password' => Hash::make($pin)]);
    }

    public function nonaktif(): static
    {
        return $this->state(fn (): array => ['aktif' => false]);
    }

    public function denganPeran(Peran ...$peran): static
    {
        return $this->afterCreating(function (User $user) use ($peran): void {
            $user->roles()->syncWithoutDetaching(
                array_map(fn (Peran $p): int => Role::untuk($p)->getKey(), $peran),
            );
        });
    }

    public function siswa(): static
    {
        return $this->denganPeran(Peran::Siswa);
    }

    public function guru(): static
    {
        return $this->state(fn (): array => ['username' => 'guru-'.fake()->unique()->numerify('####')])
            ->denganPeran(Peran::Guru);
    }

    public function peneliti(): static
    {
        return $this->denganPeran(Peran::Peneliti);
    }

    public function admin(): static
    {
        return $this->denganPeran(Peran::Admin);
    }

    public function observer(): static
    {
        return $this->denganPeran(Peran::Observer);
    }

    public function validator(): static
    {
        return $this->denganPeran(Peran::Validator);
    }
}
