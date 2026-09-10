<?php

namespace Database\Factories;

use App\Models\ReadinessItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReadinessItem>
 */
class ReadinessItemFactory extends Factory
{
    private static int $urutan = 0;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'urutan' => ++self::$urutan,
            'pertanyaan' => fake()->sentence().' …',
            'pilihan' => ['A', 'B', 'C', 'D'],
            'kunci' => fake()->numberBetween(0, 3),
            'prasyarat' => fake()->randomElement(['koordinat', 'bangun_datar', 'simetri', 'bilangan_bulat']),
        ];
    }
}
