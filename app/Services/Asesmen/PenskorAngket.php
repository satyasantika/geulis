<?php

namespace App\Services\Asesmen;

/**
 * Penskoran Angket Profil Belajar dan Angket Minat Konteks Budaya.
 *
 * Profil: tiap modus diberi skor kecenderungan 0–100 dari jawaban Likert
 * 1–4 pada butir-butir modus itu: (jumlah − n) / (3n) × 100. Jadi 0 berarti
 * "sangat tidak setuju" di semua butir, 100 berarti "sangat setuju" di semua.
 * Penentuan modus utama/campuran BUKAN di sini, melainkan di
 * DifferentiationEngine::tempatkan() supaya satu aturan, satu tempat.
 *
 * Minat: artefak dengan jumlah skor tertinggi; seri dimenangkan urutan
 * pertama pada daftar (batik, payung_geulis, anyaman).
 */
final class PenskorAngket
{
    /**
     * @param  list<array{modus: string}>  $butir
     * @param  array<int, int>  $jawaban  indeks butir => skor 1..4
     * @return array{visual: int, simbolik: int, naratif: int}
     */
    public function profil(array $butir, array $jawaban): array
    {
        $jumlah = ['visual' => 0, 'simbolik' => 0, 'naratif' => 0];
        $n = ['visual' => 0, 'simbolik' => 0, 'naratif' => 0];

        foreach ($butir as $i => $b) {
            $skor = (int) ($jawaban[$i] ?? 0);
            if ($skor < 1 || $skor > 4) {
                continue;
            }
            $jumlah[$b['modus']] += $skor;
            $n[$b['modus']]++;
        }

        $hasil = [];
        foreach ($jumlah as $modus => $total) {
            $hasil[$modus] = $n[$modus] === 0 ? 0 : (int) round(($total - $n[$modus]) / (3 * $n[$modus]) * 100);
        }

        return $hasil;
    }

    /**
     * @param  list<array{artefak: string}>  $butir
     * @param  array<int, int>  $jawaban
     * @return array{pilihan: string, skor: array<string, int>}
     */
    public function minat(array $butir, array $jawaban): array
    {
        $skor = [];
        foreach ($butir as $i => $b) {
            $skor[$b['artefak']] = ($skor[$b['artefak']] ?? 0) + (int) ($jawaban[$i] ?? 0);
        }

        $pilihan = array_key_first($skor) ?? 'batik';
        foreach ($skor as $artefak => $total) {
            if ($total > $skor[$pilihan]) {
                $pilihan = $artefak;
            }
        }

        return ['pilihan' => (string) $pilihan, 'skor' => $skor];
    }
}
