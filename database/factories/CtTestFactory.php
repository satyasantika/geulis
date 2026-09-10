<?php

namespace Database\Factories;

use App\Models\CtItem;
use App\Models\CtTest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CtTest>
 */
class CtTestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['jenis' => 'pretest', 'judul' => 'Tes CT', 'durasi_menit' => 60, 'aktif' => true];
    }

    public function posttest(): static
    {
        return $this->state(fn (): array => ['jenis' => 'posttest']);
    }

    /** Dua PG + dua uraian, satu tiap indikator (D, P PG; A, Al uraian). */
    public function denganButir(): static
    {
        return $this->afterCreating(function (CtTest $tes): void {
            CtItem::factory()->for($tes, 'test')->create(['urutan' => 1, 'indikator' => 'D', 'tipe' => 'pg', 'pilihan' => ['a', 'b', 'c'], 'kunci' => 'b']);
            CtItem::factory()->for($tes, 'test')->create(['urutan' => 2, 'indikator' => 'P', 'tipe' => 'pg', 'pilihan' => ['x', 'y', 'z'], 'kunci' => 'x']);
            CtItem::factory()->for($tes, 'test')->uraian()->create(['urutan' => 3, 'indikator' => 'A']);
            CtItem::factory()->for($tes, 'test')->uraian()->create(['urutan' => 4, 'indikator' => 'Al']);
        });
    }
}
