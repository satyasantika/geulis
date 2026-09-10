<?php

namespace App\Services\Asesmen;

/**
 * Penskoran Tes Kesiapan Prasyarat: R = (benar / jumlah butir) × 100,
 * dibulatkan ke bilangan bulat, plus rincian per prasyarat untuk guru.
 * Logika murni.
 */
final class PenskorKesiapan
{
    /**
     * @param  list<array{prasyarat: string, benar: bool}>  $jawaban
     * @return array{skor: int, benar: int, total: int, per_prasyarat: array<string, array{benar: int, total: int}>}
     */
    public function skor(array $jawaban): array
    {
        $total = count($jawaban);
        $benar = 0;
        $perPrasyarat = [];

        foreach ($jawaban as $j) {
            $perPrasyarat[$j['prasyarat']] ??= ['benar' => 0, 'total' => 0];
            $perPrasyarat[$j['prasyarat']]['total']++;
            if ($j['benar']) {
                $benar++;
                $perPrasyarat[$j['prasyarat']]['benar']++;
            }
        }

        return [
            'skor' => $total === 0 ? 0 : (int) round($benar / $total * 100),
            'benar' => $benar,
            'total' => $total,
            'per_prasyarat' => $perPrasyarat,
        ];
    }
}
