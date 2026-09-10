<?php

use App\Services\Konten\PenskorKuis;

$butir = [
    ['pertanyaan' => 'a', 'pilihan' => ['x', 'y'], 'kunci' => 0, 'label' => 'tanda ordinat'],
    ['pertanyaan' => 'b', 'pilihan' => ['x', 'y'], 'kunci' => 1],
    ['pertanyaan' => 'c', 'pilihan' => ['x', 'y'], 'kunci' => 1, 'label' => 'sumbu y=x'],
];

it('returns the proportion correct and labels the wrong items', function () use ($butir): void {
    $hasil = (new PenskorKuis)->skor($butir, [0 => 1, 1 => 1, 2 => 0]);

    expect($hasil['proporsi'])->toBe(0.333)
        ->and($hasil['benar'])->toBe(1)
        ->and($hasil['total'])->toBe(3)
        ->and($hasil['butir_salah'])->toBe([
            ['indeks' => 0, 'label' => 'tanda ordinat'],
            ['indeks' => 2, 'label' => 'sumbu y=x'],
        ]);
});

it('treats a missing answer as wrong', function () use ($butir): void {
    expect((new PenskorKuis)->skor($butir, [0 => 0])['benar'])->toBe(1);
});

it('computes the median duration ignoring empty values', function (): void {
    $p = new PenskorKuis;

    expect($p->median([300, 100, 200]))->toBe(200)
        ->and($p->median([100, 400]))->toBe(250)
        ->and($p->median([0, null]))->toBeNull()
        ->and($p->median([]))->toBeNull();
});
