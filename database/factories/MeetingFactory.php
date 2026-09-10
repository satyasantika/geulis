<?php

namespace Database\Factories;

use App\Models\Meeting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meeting>
 */
class MeetingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'urutan' => fake()->unique()->numberBetween(1, 200),
            'judul' => 'Pertemuan '.fake()->word(),
            'materi' => 'refleksi',
            'artefak_utama' => 'batik',
            'fokus_ct' => 'dekomposisi',
            'capaian_pembelajaran' => fake()->sentence(),
            'terbit' => true,
        ];
    }
}
