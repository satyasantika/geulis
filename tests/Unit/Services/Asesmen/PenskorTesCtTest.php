<?php

use App\Services\Asesmen\PenskorTesCt;

it('scores multiple choice and short answers automatically, tolerant of spacing and minus signs', function (): void {
    $p = new PenskorTesCt;
    $pg = ['tipe' => 'pg', 'kunci' => '(3, 1)', 'skor_maks' => 4];
    $isian = ['tipe' => 'isian', 'kunci' => '-3', 'skor_maks' => 4];

    expect($p->skorOtomatis($pg, '(3, 1)'))->toBe(4.0)
        ->and($p->skorOtomatis($pg, '(3,1)'))->toBe(4.0)
        ->and($p->skorOtomatis($pg, '(1, 3)'))->toBe(0.0)
        ->and($p->skorOtomatis($pg, null))->toBe(0.0)
        ->and($p->skorOtomatis($isian, ' −3 '))->toBe(4.0)
        ->and($p->skorOtomatis(['tipe' => 'uraian', 'kunci' => null, 'skor_maks' => 4], 'jawaban'))->toBeNull();
});

it('summarises per indicator and flags an incomplete test when an essay is ungraded', function (): void {
    $rekap = (new PenskorTesCt)->rekap([
        ['indikator' => 'D', 'skor_maks' => 4, 'skor' => 4.0],
        ['indikator' => 'D', 'skor_maks' => 4, 'skor' => 2.0],
        ['indikator' => 'P', 'skor_maks' => 4, 'skor' => 0.0],
        ['indikator' => 'A', 'skor_maks' => 4, 'skor' => null],
        ['indikator' => 'Al', 'skor_maks' => 4, 'skor' => 3.0],
    ]);

    expect($rekap['skor_d'])->toBe(6.0)
        ->and($rekap['skor_total'])->toBe(9.0)
        ->and($rekap['maks_total'])->toBe(20)
        ->and($rekap['persen'])->toBe(45.0)
        ->and($rekap['lengkap'])->toBeFalse()
        ->and($rekap['per_indikator']['D']['persen'])->toBe(75.0);
});
