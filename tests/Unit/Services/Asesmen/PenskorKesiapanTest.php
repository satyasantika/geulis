<?php

use App\Services\Asesmen\PenskorKesiapan;

it('scores readiness as a rounded percentage with a breakdown per prerequisite', function (): void {
    $jawaban = [
        ['prasyarat' => 'koordinat', 'benar' => true],
        ['prasyarat' => 'koordinat', 'benar' => false],
        ['prasyarat' => 'simetri', 'benar' => true],
    ];

    $hasil = (new PenskorKesiapan)->skor($jawaban);

    expect($hasil['skor'])->toBe(67)
        ->and($hasil['benar'])->toBe(2)
        ->and($hasil['total'])->toBe(3)
        ->and($hasil['per_prasyarat'])->toBe([
            'koordinat' => ['benar' => 1, 'total' => 2],
            'simetri' => ['benar' => 1, 'total' => 1],
        ]);
});

it('returns zero when nothing was answered', function (): void {
    expect((new PenskorKesiapan)->skor([])['skor'])->toBe(0);
});
