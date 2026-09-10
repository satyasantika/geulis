<?php

namespace Database\Factories;

use App\Models\CtItem;
use App\Models\CtTest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CtItem>
 */
class CtItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ct_test_id' => CtTest::factory(),
            'urutan' => fake()->unique()->numberBetween(1, 500),
            'indikator' => 'D',
            'stimulus' => 'Perhatikan motif batik berikut.',
            'pertanyaan' => fake()->sentence().' …',
            'tipe' => 'pg',
            'pilihan' => ['a', 'b', 'c', 'd'],
            'kunci' => 'a',
            'skor_maks' => 4,
        ];
    }

    public function uraian(): static
    {
        return $this->state(fn (): array => [
            'tipe' => 'uraian', 'pilihan' => null, 'kunci' => null,
            'rubrik_butir' => ['0' => 'kosong', '1' => 'sedikit', '2' => 'sebagian', '3' => 'hampir', '4' => 'lengkap'],
        ]);
    }
}
