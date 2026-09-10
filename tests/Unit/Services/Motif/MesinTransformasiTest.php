<?php

use App\Services\Motif\MesinTransformasi;

$kotak = [[0, 0], [1, 0], [1, 1], [0, 1]];

it('draws the base motif and chains each command on the last result', function () use ($kotak): void {
    $hasil = (new MesinTransformasi)->jalankan($kotak, [
        ['op' => 'MOTIF_DASAR'],
        ['op' => 'TRANSLASI', 'vektor' => [2, 0]],
        ['op' => 'TRANSLASI', 'vektor' => [2, 0]],
    ]);

    expect($hasil)->toHaveCount(3)
        ->and($hasil[1][0])->toBe([2.0, 0.0])
        ->and($hasil[2][0])->toBe([4.0, 0.0]);
});

it('reflects, rotates, and dilates points correctly', function (): void {
    $m = new MesinTransformasi;
    $titik = [[2, 3], [2, 3], [2, 3]];

    expect($m->jalankan($titik, [['op' => 'MOTIF_DASAR'], ['op' => 'REFLEKSI', 'garis' => 'x']])[1][0])->toBe([2.0, -3.0])
        ->and($m->jalankan($titik, [['op' => 'MOTIF_DASAR'], ['op' => 'REFLEKSI', 'garis' => 'y=x']])[1][0])->toBe([3.0, 2.0])
        ->and($m->jalankan($titik, [['op' => 'MOTIF_DASAR'], ['op' => 'REFLEKSI', 'garis' => 'y=-x']])[1][0])->toBe([-3.0, -2.0])
        ->and($m->jalankan($titik, [['op' => 'MOTIF_DASAR'], ['op' => 'ROTASI', 'pusat' => [0, 0], 'sudut' => 90]])[1][0])->toBe([-3.0, 2.0])
        ->and($m->jalankan($titik, [['op' => 'MOTIF_DASAR'], ['op' => 'DILATASI', 'pusat' => [1, 1], 'k' => 2]])[1][0])->toBe([3.0, 5.0]);
});

it('repeats a body n times and then treats everything born in the loop as the current group', function () use ($kotak): void {
    $hasil = (new MesinTransformasi)->jalankan($kotak, [
        ['op' => 'MOTIF_DASAR'],
        ['op' => 'ULANGI', 'n' => 2, 'badan' => [['op' => 'TRANSLASI', 'vektor' => [2, 0]]]],
        ['op' => 'ULANGI', 'n' => 2, 'badan' => [['op' => 'TRANSLASI', 'vektor' => [0, 2]]]],
    ]);

    expect($hasil)->toHaveCount(9) // kisi 3 × 3
        ->and(collect($hasil)->map(fn ($p) => $p[0])->contains([4.0, 4.0]))->toBeTrue();
});

it('counts steps without the base motif and including loop bodies, and flattens for evidence', function (): void {
    $m = new MesinTransformasi;
    $perintah = [
        ['op' => 'MOTIF_DASAR'],
        ['op' => 'ULANGI', 'n' => 8, 'badan' => [['op' => 'ROTASI', 'pusat' => [0, 0], 'sudut' => 45]]],
        ['op' => 'REFLEKSI', 'garis' => 'x'],
    ];

    expect($m->hitungLangkah($perintah))->toBe(3)
        ->and(array_column($m->ratakan($perintah), 'op'))->toBe(['MOTIF_DASAR', 'ULANGI', 'ROTASI', 'REFLEKSI']);
});

it('rejects malformed commands with a student-readable message', function (array $perintah, string $pesan): void {
    expect(fn () => (new MesinTransformasi)->validasi([$perintah]))->toThrow(InvalidArgumentException::class, $pesan);
})->with([
    'op asing' => [['op' => 'HAPUS'], 'tidak dikenal'],
    'vektor bukan pasangan' => [['op' => 'TRANSLASI', 'vektor' => [1]], 'pasangan angka'],
    'garis asing' => [['op' => 'REFLEKSI', 'garis' => 'z'], 'Garis refleksi'],
    'k nol' => [['op' => 'DILATASI', 'pusat' => [0, 0], 'k' => 0], 'bukan nol'],
    'ulangi 0' => [['op' => 'ULANGI', 'n' => 0, 'badan' => []], '1–36'],
]);

it('caps the number of shapes so a runaway loop cannot exhaust the server', function () use ($kotak): void {
    expect(fn () => (new MesinTransformasi)->jalankan($kotak, [
        ['op' => 'MOTIF_DASAR'],
        ['op' => 'ULANGI', 'n' => 36, 'badan' => [['op' => 'ULANGI', 'n' => 36, 'badan' => [['op' => 'TRANSLASI', 'vektor' => [0.1, 0]]]]]],
    ]))->toThrow(InvalidArgumentException::class, 'Terlalu banyak bangun');
});
