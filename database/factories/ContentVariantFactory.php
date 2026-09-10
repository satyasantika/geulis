<?php

namespace Database\Factories;

use App\Models\ContentVariant;
use App\Models\LessonUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentVariant>
 */
class ContentVariantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lesson_unit_id' => LessonUnit::factory(),
            'level' => '*',
            'modus' => '*',
            'judul' => fake()->sentence(4),
            'badan_konten' => '<p>'.fake()->paragraph().'</p>',
            'status' => 'siap',
        ];
    }

    public function untuk(string $level, string $modus = '*'): static
    {
        return $this->state(fn (): array => ['level' => $level, 'modus' => $modus]);
    }
}
