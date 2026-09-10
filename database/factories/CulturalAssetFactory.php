<?php

namespace Database\Factories;

use App\Models\CulturalAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CulturalAsset>
 */
class CulturalAssetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_motif' => fake()->randomElement(['Sawoan', 'Merak Ngibing', 'Rozet Payung', 'Kisi Rajapolah']),
            'artefak' => fake()->randomElement(['batik', 'payung_geulis', 'anyaman']),
            'perajin_sumber' => 'Ibu '.fake()->firstName(),
            'lokasi' => fake()->randomElement(['Cigeureung, Kota Tasikmalaya', 'Panyingkiran, Kota Tasikmalaya', 'Rajapolah, Kab. Tasikmalaya']),
            'tanggal_dokumentasi' => '2026-05-12',
            'izin_diperoleh' => true,
            'berkas' => 'aset-budaya/contoh.webp',
            'catatan_matematis' => 'Motif memiliki simetri lipat terhadap dua sumbu.',
        ];
    }

    public function tanpaIzin(): static
    {
        return $this->state(fn (): array => ['izin_diperoleh' => false]);
    }
}
