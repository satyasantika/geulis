<?php

use App\Services\Rubrik\PenskorRubrik;

it('computes a weighted 0–100 score from four-level criteria', function (): void {
    $hasil = (new PenskorRubrik)->skor([
        ['bobot' => 25, 'tingkat' => 4],
        ['bobot' => 25, 'tingkat' => 3],
        ['bobot' => 25, 'tingkat' => 2],
        ['bobot' => 25, 'tingkat' => 3],
    ]);

    expect($hasil)->toBe(['skor' => 75.0, 'lengkap' => true, 'dinilai' => 4, 'total' => 4]);
});

it('reports a partial score and incompleteness when a criterion is missing', function (): void {
    $hasil = (new PenskorRubrik)->skor([
        ['bobot' => 50, 'tingkat' => 4],
        ['bobot' => 50, 'tingkat' => null],
    ]);

    expect($hasil['skor'])->toBe(100.0)
        ->and($hasil['lengkap'])->toBeFalse()
        ->and((new PenskorRubrik)->skor([])['skor'])->toBeNull();
});
