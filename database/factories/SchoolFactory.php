<?php

namespace Database\Factories;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<School>
 */
class SchoolFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => 'SMAN '.fake()->numberBetween(1, 12).' '.fake()->randomElement(['Tasikmalaya', 'Ciamis', 'Banjar']),
            'npsn' => fake()->unique()->numerify('########'),
            'kabupaten_kota' => fake()->randomElement(['Kota Tasikmalaya', 'Ciamis', 'Banjar']),
        ];
    }
}
