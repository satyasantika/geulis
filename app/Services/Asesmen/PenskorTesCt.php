<?php

namespace App\Services\Asesmen;

/**
 * Penskoran tes CT — logika murni.
 *
 *  - pg     : benar → skor_maks, salah/kosong → 0
 *  - isian  : cocok teks setelah normalisasi (huruf kecil, spasi, tanda −/-) → skor_maks
 *  - uraian : tidak dinilai di sini (manual dengan rubrik butir)
 *
 * Rekap per indikator menjumlahkan skor berlaku; `persen` = total / maksimum × 100
 * (dasar N-Gain). Uraian yang belum dinilai dihitung 0 dan ditandai `lengkap = false`.
 */
final class PenskorTesCt
{
    /**
     * @param  array{tipe: string, kunci: string|null, skor_maks: int}  $butir
     */
    public function skorOtomatis(array $butir, ?string $jawaban): ?float
    {
        if ($jawaban === null || trim($jawaban) === '') {
            return $butir['tipe'] === 'uraian' ? null : 0.0;
        }

        return match ($butir['tipe']) {
            'pg' => $this->normal($jawaban) === $this->normal((string) $butir['kunci']) ? (float) $butir['skor_maks'] : 0.0,
            'isian' => $this->normal($jawaban) === $this->normal((string) $butir['kunci']) ? (float) $butir['skor_maks'] : 0.0,
            default => null,
        };
    }

    /**
     * @param  list<array{indikator: string, skor_maks: int, skor: float|null}>  $baris  satu per butir
     * @return array{skor_d: float, skor_p: float, skor_a: float, skor_al: float, skor_total: float, maks_total: int, persen: float, lengkap: bool, per_indikator: array<string, array{skor: float, maks: int, persen: float}>}
     */
    public function rekap(array $baris): array
    {
        $per = ['D' => ['skor' => 0.0, 'maks' => 0], 'P' => ['skor' => 0.0, 'maks' => 0], 'A' => ['skor' => 0.0, 'maks' => 0], 'Al' => ['skor' => 0.0, 'maks' => 0]];
        $lengkap = true;

        foreach ($baris as $b) {
            $ind = $b['indikator'];
            $per[$ind] ??= ['skor' => 0.0, 'maks' => 0];
            $per[$ind]['maks'] += (int) $b['skor_maks'];
            if ($b['skor'] === null) {
                $lengkap = false;
            } else {
                $per[$ind]['skor'] += (float) $b['skor'];
            }
        }

        $total = array_sum(array_column($per, 'skor'));
        $maks = array_sum(array_column($per, 'maks'));

        foreach ($per as $k => $v) {
            $per[$k]['persen'] = $v['maks'] > 0 ? round($v['skor'] / $v['maks'] * 100, 2) : 0.0;
        }

        return [
            'skor_d' => $per['D']['skor'], 'skor_p' => $per['P']['skor'], 'skor_a' => $per['A']['skor'], 'skor_al' => $per['Al']['skor'],
            'skor_total' => $total,
            'maks_total' => $maks,
            'persen' => $maks > 0 ? round($total / $maks * 100, 2) : 0.0,
            'lengkap' => $lengkap,
            'per_indikator' => $per,
        ];
    }

    private function normal(string $s): string
    {
        $s = mb_strtolower(trim($s));
        $s = str_replace(['−', '–', '—'], '-', $s);

        return preg_replace('/\s+/', '', $s) ?? $s;
    }
}
