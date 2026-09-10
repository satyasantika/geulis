<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\LessonUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lesson_unit_id' => LessonUnit::factory(),
            'tipe' => 'kuis',
            'judul' => 'Kuis',
            'konfigurasi' => ['butir' => self::butirContoh(4)],
            'skor_maks' => 100,
            'level' => '*',
        ];
    }

    /**
     * @return list<array{pertanyaan: string, pilihan: list<string>, kunci: int, label: string}>
     */
    public static function butirContoh(int $n): array
    {
        return array_map(fn (int $i) => [
            'pertanyaan' => "Bayangan titik ({$i}, 2) oleh refleksi terhadap sumbu-x adalah …",
            'pilihan' => ["({$i}, −2)", "(−{$i}, 2)", "(2, {$i})", "(−{$i}, −2)"],
            'kunci' => 0,
            'label' => "refleksi sumbu-x #{$i}",
        ], range(1, $n));
    }

    public function kuis(int $jumlahButir = 4): static
    {
        return $this->state(fn (): array => ['tipe' => 'kuis', 'konfigurasi' => ['butir' => self::butirContoh($jumlahButir)]]);
    }
}
