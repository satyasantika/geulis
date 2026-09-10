<?php

use App\Services\Asesmen\PenskorAngket;

$butirProfil = [
    ['modus' => 'visual'], ['modus' => 'simbolik'], ['modus' => 'naratif'],
    ['modus' => 'visual'], ['modus' => 'simbolik'], ['modus' => 'naratif'],
];

it('maps Likert 1–4 to a 0–100 tendency per mode', function () use ($butirProfil): void {
    $skor = (new PenskorAngket)->profil($butirProfil, [4, 1, 3, 4, 1, 2]);

    expect($skor)->toBe(['visual' => 100, 'simbolik' => 0, 'naratif' => 50]);
});

it('ignores unanswered or out-of-range items', function () use ($butirProfil): void {
    $skor = (new PenskorAngket)->profil($butirProfil, [4, 9, 3]);

    expect($skor['visual'])->toBe(100)
        ->and($skor['simbolik'])->toBe(0)
        ->and($skor['naratif'])->toBe(67);
});

it('picks the artefact with the highest interest and breaks ties by list order', function (): void {
    $butir = [['artefak' => 'batik'], ['artefak' => 'payung_geulis'], ['artefak' => 'anyaman'], ['artefak' => 'batik'], ['artefak' => 'payung_geulis'], ['artefak' => 'anyaman']];

    expect((new PenskorAngket)->minat($butir, [2, 4, 3, 2, 4, 3])['pilihan'])->toBe('payung_geulis')
        ->and((new PenskorAngket)->minat($butir, [3, 3, 3, 3, 3, 3])['pilihan'])->toBe('batik')
        ->and((new PenskorAngket)->minat($butir, [1, 1, 4, 1, 1, 4])['skor'])->toBe(['batik' => 2, 'payung_geulis' => 2, 'anyaman' => 8]);
});
