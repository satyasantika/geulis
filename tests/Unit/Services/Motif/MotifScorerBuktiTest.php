<?php

use App\Services\Motif\MotifScorer;

it('accepts any of several valid parameter values and reads the expected mark count from the key', function (): void {
    $kunci = [
        'motif_dasar' => [0, 1, 2, 3],
        'jumlah_motif_dasar' => 1,
        'n_pengulangan' => 0,
        'parameter' => ['REFLEKSI' => ['garis' => ['salah_satu' => ['x', 'y']]]],
    ];

    $bukti = (new MotifScorer)->buktiCT([
        ['op' => 'REFLEKSI', 'garis' => 'y'],
        ['op' => 'REFLEKSI', 'garis' => 'x'],
        ['op' => 'REFLEKSI', 'garis' => 'y=x'],
    ], [2], $kunci);

    expect($bukti['abstraksi'])->toBe(['parameter_benar' => 2, 'parameter_total' => 3, 'proporsi' => 0.667])
        ->and($bukti['dekomposisi'])->toBe(['ditandai' => 1, 'diharapkan' => 1, 'tepat' => 1])
        ->and($bukti['pengenalan_pola']['benar'])->toBeTrue()
        ->and($bukti['algoritma']['memakai_perulangan'])->toBeFalse();
});

it('compares vector parameters element by element', function (): void {
    $kunci = ['parameter' => ['TRANSLASI' => ['vektor' => [4, 0]], 'ROTASI' => ['pusat' => [0, 0], 'sudut' => 45]], 'n_pengulangan' => 8];

    $bukti = (new MotifScorer)->buktiCT([
        ['op' => 'ULANGI', 'n' => 8],
        ['op' => 'ROTASI', 'pusat' => [0, 0], 'sudut' => 45],
        ['op' => 'TRANSLASI', 'vektor' => [4, 1]],
    ], [], $kunci);

    expect($bukti['abstraksi']['parameter_benar'])->toBe(2)
        ->and($bukti['abstraksi']['parameter_total'])->toBe(3)
        ->and($bukti['pengenalan_pola'])->toBe(['n_pengulangan' => 8, 'benar' => true])
        ->and($bukti['algoritma']['memakai_perulangan'])->toBeTrue();
});
