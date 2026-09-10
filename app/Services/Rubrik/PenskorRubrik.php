<?php

namespace App\Services\Rubrik;

/**
 * Skor rubrik analitik: rerata tertimbang tingkat (1–4) per kriteria,
 * dinyatakan pada skala 0–100. Logika murni.
 */
final class PenskorRubrik
{
    /**
     * @param  list<array{bobot: int, tingkat: int|null, tingkat_maks?: int}>  $kriteria
     * @return array{skor: float|null, lengkap: bool, dinilai: int, total: int}
     */
    public function skor(array $kriteria): array
    {
        $bobotDinilai = 0;
        $bobotTotal = 0;
        $jumlah = 0.0;
        $dinilai = 0;

        foreach ($kriteria as $k) {
            $bobotTotal += (int) $k['bobot'];
            if ($k['tingkat'] === null) {
                continue;
            }
            $maks = (int) ($k['tingkat_maks'] ?? 4);
            $jumlah += (int) $k['bobot'] * ((int) $k['tingkat'] / $maks);
            $bobotDinilai += (int) $k['bobot'];
            $dinilai++;
        }

        return [
            'skor' => $bobotDinilai > 0 ? round($jumlah / $bobotDinilai * 100, 2) : null,
            'lengkap' => $dinilai === count($kriteria) && count($kriteria) > 0,
            'dinilai' => $dinilai,
            'total' => count($kriteria),
        ];
    }
}
