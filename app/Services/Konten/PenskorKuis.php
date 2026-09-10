<?php

namespace App\Services\Konten;

/**
 * Penskoran kuis pilihan ganda (latihan & pemeriksaan penguasaan).
 * Logika murni; mengembalikan proporsi 0..1 dan daftar butir yang salah
 * (indeks + label) — bahan `butir_salah` untuk Mesin Diferensiasi dan
 * latihan penguatan.
 */
final class PenskorKuis
{
    /**
     * @param  list<array{pertanyaan: string, pilihan: list<string>, kunci: int, label?: string}>  $butir
     * @param  array<int, int|null>  $jawaban  indeks butir => indeks pilihan
     * @return array{proporsi: float, benar: int, total: int, butir_salah: list<array{indeks: int, label: string}>}
     */
    public function skor(array $butir, array $jawaban): array
    {
        $total = count($butir);
        $benar = 0;
        $salah = [];

        foreach ($butir as $i => $b) {
            if (isset($jawaban[$i]) && (int) $jawaban[$i] === (int) $b['kunci']) {
                $benar++;
            } else {
                $salah[] = ['indeks' => $i, 'label' => $b['label'] ?? 'butir '.($i + 1)];
            }
        }

        return [
            'proporsi' => $total === 0 ? 0.0 : round($benar / $total, 3),
            'benar' => $benar,
            'total' => $total,
            'butir_salah' => $salah,
        ];
    }

    /**
     * Median durasi (detik) dari daftar durasi; null bila kosong.
     *
     * @param  list<int>  $durasi
     */
    public function median(array $durasi): ?int
    {
        $durasi = array_values(array_filter($durasi, fn ($d) => $d !== null && $d > 0));
        if ($durasi === []) {
            return null;
        }
        sort($durasi);
        $n = count($durasi);
        $tengah = intdiv($n, 2);

        return $n % 2 === 1 ? (int) $durasi[$tengah] : (int) round(($durasi[$tengah - 1] + $durasi[$tengah]) / 2);
    }
}
