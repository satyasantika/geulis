<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Classroom>
 */
class ClassroomFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'guru_id' => User::factory()->guru(),
            'nama' => 'XI MIPA '.fake()->numberBetween(1, 6),
            'tahun_ajaran' => '2026/2027',
            'kelompok_riset' => 'non_riset',
            'aktif' => true,
        ];
    }

    public function eksperimen(): static
    {
        return $this->state(fn (): array => ['kelompok_riset' => 'eksperimen']);
    }

    public function kontrol(): static
    {
        return $this->state(fn (): array => ['kelompok_riset' => 'kontrol']);
    }
}
