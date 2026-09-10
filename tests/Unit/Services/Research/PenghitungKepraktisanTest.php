<?php

use App\Services\Research\PenghitungKepraktisan;

it('reverses negative items and categorises the mean per aspect and overall', function (): void {
    $rekap = (new PenghitungKepraktisan(4))->rekap([
        ['aspek' => 'Kemudahan', 'butir_negatif' => false, 'skor' => [4, 4, 3]],
        ['aspek' => 'Kemudahan', 'butir_negatif' => true, 'skor' => [1, 1, 2]],   // dibalik → 4, 4, 3
        ['aspek' => 'Kemanfaatan', 'butir_negatif' => false, 'skor' => [2, 2, 2]],
    ]);

    expect($rekap['n_responden'])->toBe(3)
        ->and($rekap['per_aspek']['Kemudahan'])->toBe(['rerata' => 3.67, 'kategori' => 'sangat praktis', 'n_butir' => 2])
        ->and($rekap['per_aspek']['Kemanfaatan']['kategori'])->toBe('kurang praktis')
        ->and($rekap['rerata'])->toBe(3.11)
        ->and($rekap['kategori'])->toBe('praktis');
});

it('returns nulls when nobody has answered', function (): void {
    $rekap = (new PenghitungKepraktisan)->rekap([['aspek' => 'A', 'butir_negatif' => false, 'skor' => []]]);

    expect($rekap['rerata'])->toBeNull()->and($rekap['kategori'])->toBeNull();
});
