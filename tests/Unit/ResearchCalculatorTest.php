<?php

use App\Services\Motif\MotifScorer;
use App\Services\Research\AikenCalculator;
use App\Services\Research\NGainCalculator;

/**
 * Penghitung instrumen penelitian.
 *
 * Angka-angka pada uji ini dihitung tangan lebih dahulu. Kalau nanti ada yang
 * "memperbaiki" rumusnya dan uji ini merah, rumusnya yang salah, bukan ujinya.
 */

// ---------- Aiken's V ----------

it('menghitung V satu butir sesuai rumus Aiken', function () {
    // skor 5,5,4,5,4 pada skala 1-5, 5 penilai
    // s = 4+4+3+4+3 = 18 ; pembagi = 5 x (5-1) = 20 ; V = 0,9
    $h = (new AikenCalculator)->hitungButir([5, 5, 4, 5, 4]);
    expect($h['nilai_v'])->toBe(0.9)
        ->and($h['kategori'])->toBe('tinggi')
        ->and($h['n_penilai'])->toBe(5);
});

it('mengategorikan V rendah dengan benar', function () {
    // s = 1+2+1+2+1 = 7 ; /20 = 0,35
    $h = (new AikenCalculator)->hitungButir([2, 3, 2, 3, 2]);
    expect($h['nilai_v'])->toBe(0.35)->and($h['kategori'])->toBe('rendah');
});

it('menolak skor di luar rentang skala', function () {
    (new AikenCalculator)->hitungButir([5, 6]);
})->throws(InvalidArgumentException::class);

it('menolak butir yang belum dinilai siapa pun', function () {
    (new AikenCalculator)->hitungButir([]);
})->throws(InvalidArgumentException::class);

it('merata-ratakan V beberapa butir', function () {
    $a = new AikenCalculator;
    $rerata = $a->rerata([$a->hitungButir([5, 5, 4, 5, 4]), $a->hitungButir([2, 3, 2, 3, 2])]);
    expect($rerata['nilai_v'])->toBe(0.625)->and($rerata['n_butir'])->toBe(2);
});

// ---------- N-Gain ----------

it('menghitung N-Gain sesuai rumus Hake', function () {
    // (80 - 40) / (100 - 40) = 0,667
    $h = (new NGainCalculator)->hitung(40.0, 80.0);
    expect($h['n_gain'])->toBe(0.667)->and($h['kategori'])->toBe('sedang');
});

it('mengembalikan null bila pretest sudah maksimum', function () {
    expect((new NGainCalculator)->hitung(100.0, 100.0)['n_gain'])->toBeNull();
});

it('meringkas satu kelompok dengan statistik deskriptif', function () {
    $s = (new NGainCalculator)->ringkasKelompok([
        ['pretest' => 40, 'posttest' => 80],
        ['pretest' => 50, 'posttest' => 75],
        ['pretest' => 30, 'posttest' => 60],
    ]);
    expect($s['n'])->toBe(3)
        ->and($s['rerata'])->toBe(0.532)
        ->and($s['sd'])->toBe(0.122)
        ->and($s['kategori'])->toBe('sedang');
});

// ---------- Motif Builder ----------

it('menghitung kemiripan dengan Intersection over Union', function () {
    $sasaran = array_fill(0, 100, false);
    for ($i = 0; $i < 50; $i++) {
        $sasaran[$i] = true;
    }

    $siswa = array_fill(0, 100, false);
    for ($i = 0; $i < 48; $i++) {
        $siswa[$i] = true;
    }

    // irisan 48, gabungan 50 -> 96%
    $h = (new MotifScorer)->nilai($siswa, $sasaran, langkahSiswa: 4, langkahMinimum: 3);
    expect($h['skor_kemiripan'])->toBe(96.0)
        ->and($h['lolos'])->toBeTrue()
        ->and($h['efisiensi'])->toBe(75.0);
});

it('tidak meluluskan hasil di bawah ambang kemiripan', function () {
    $sasaran = array_fill(0, 100, true);
    $siswa = array_fill(0, 100, false);
    for ($i = 0; $i < 50; $i++) {
        $siswa[$i] = true;
    }

    expect((new MotifScorer)->nilai($siswa, $sasaran, 3, 3)['lolos'])->toBeFalse();
});

it('mengekstraksi bukti empat indikator CT dari satu kiriman', function () {
    $bukti = (new MotifScorer)->buktiCT(
        urutanPerintah: [
            ['op' => 'MOTIF_DASAR'],
            ['op' => 'ULANGI', 'n' => 8],
            ['op' => 'ROTASI', 'pusat' => [0, 0], 'sudut' => 45],
            ['op' => 'DILATASI', 'pusat' => [0, 0], 'k' => 0.6],
        ],
        motifDasarDitandai: ['jari'],
        kunci: [
            'motif_dasar' => ['jari'],
            'n_pengulangan' => 8,
            'parameter' => [
                'ROTASI' => ['sudut' => 45, 'pusat' => [0, 0]],
                'DILATASI' => ['k' => 0.6, 'pusat' => [0, 0]],
            ],
        ],
    );

    expect($bukti['dekomposisi']['tepat'])->toBe(1)
        ->and($bukti['pengenalan_pola']['benar'])->toBeTrue()
        ->and($bukti['abstraksi']['proporsi'])->toBe(1.0)
        ->and($bukti['algoritma']['memakai_perulangan'])->toBeTrue();
});
