<?php

namespace Database\Factories;

use App\Models\LessonUnit;
use App\Models\Meeting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LessonUnit>
 */
class LessonUnitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'meeting_id' => Meeting::factory(),
            'urutan' => fake()->unique()->numberBetween(1, 250),
            'judul' => 'Unit',
            'tipe' => 'formalisasi',
            'memicu_adaptasi' => false,
        ];
    }
}
