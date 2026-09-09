<?php

use App\Services\Differentiation\AdaptationDecision;
use App\Services\Differentiation\DifferentiationConfig;
use App\Services\Differentiation\DifferentiationEngine;

/**
 * Setiap aturan Mesin Diferensiasi WAJIB punya kasus uji.
 *
 * Ini bagian kode paling berbahaya bila salah: kesalahannya tidak terlihat
 * di layar, dan baru ketahuan setelah data satu semester terlanjur terkumpul.
 */
function engine(): DifferentiationEngine
{
    return new DifferentiationEngine(new DifferentiationConfig);
}

// ---------- Penempatan awal ----------

it('menempatkan siswa berskor rendah ke L1', function () {
    $hasil = engine()->tempatkan(45, ['visual' => 70, 'simbolik' => 40, 'naratif' => 50], 'batik');
    expect($hasil['level_awal'])->toBe('L1');
    expect($hasil['modus'])->toBe('visual');
});

it('menempatkan siswa berskor sedang ke L2', function () {
    expect(engine()->tempatkan(72, ['visual' => 78, 'simbolik' => 55, 'naratif' => 61], 'payung_geulis')['level_awal'])
        ->toBe('L2');
});

it('menempatkan siswa berskor tinggi ke L3', function () {
    expect(engine()->tempatkan(85, ['visual' => 50, 'simbolik' => 80, 'naratif' => 45], 'anyaman')['level_awal'])
        ->toBe('L3');
});

it('menandai modus campuran bila dua skor teratas berdekatan', function () {
    $hasil = engine()->tempatkan(70, ['visual' => 72, 'simbolik' => 68, 'naratif' => 40], 'batik');
    expect($hasil['modus'])->toBe('campuran');
});

// ---------- Adaptasi berkelanjutan ----------

it('RULE_PROMOTE menaikkan level saat penguasaan melewati ambang', function () {
    $d = engine()->evaluasi(mLama: 0.75, skorPemeriksaan: 1.0, levelKini: 'L2');
    expect($d->kodeAturan)->toBe(AdaptationDecision::PROMOTE)
        ->and($d->levelSesudah)->toBe('L3')
        ->and($d->mSesudah)->toBe(0.9); // 0.4*0.75 + 0.6*1.0
});

it('RULE_ENRICH dipakai saat sudah di L3', function () {
    $d = engine()->evaluasi(0.85, 1.0, 'L3');
    expect($d->kodeAturan)->toBe(AdaptationDecision::ENRICH)
        ->and($d->levelSesudah)->toBe('L3');
});

it('RULE_REINFORCE menahan level pada rentang tengah', function () {
    $d = engine()->evaluasi(0.72, 0.40, 'L2');
    expect($d->kodeAturan)->toBe(AdaptationDecision::REINFORCE)
        ->and($d->levelSesudah)->toBe('L2')
        ->and($d->mSesudah)->toBe(0.528);
});

it('RULE_REMEDIATE menurunkan level satu tingkat dan menambah iterasi', function () {
    $d = engine()->evaluasi(0.528, 0.20, 'L2', iterasiRemedial: 0);
    expect($d->kodeAturan)->toBe(AdaptationDecision::REMEDIATE)
        ->and($d->levelSesudah)->toBe('L1')
        ->and($d->iterasiRemedial)->toBe(1);
});

it('RULE_ESCALATE menandai untuk guru setelah batas remedial terlampaui', function () {
    $d = engine()->evaluasi(0.30, 0.20, 'L1', iterasiRemedial: 2);
    expect($d->kodeAturan)->toBe(AdaptationDecision::ESCALATE)
        ->and($d->perluPendampingan)->toBeTrue()
        ->and($d->levelSesudah)->toBe('L1');
});

it('tidak pernah menurunkan level di bawah L1', function () {
    $d = engine()->evaluasi(0.30, 0.10, 'L1', iterasiRemedial: 0);
    expect($d->levelSesudah)->toBe('L1');
});

it('tidak pernah menaikkan level di atas L3', function () {
    $d = engine()->evaluasi(0.95, 1.0, 'L3');
    expect($d->levelSesudah)->toBe('L3');
});

it('memindahkan level paling banyak satu tingkat per pemeriksaan', function () {
    $d = engine()->evaluasi(0.10, 0.00, 'L3', iterasiRemedial: 0);
    expect($d->levelSesudah)->toBe('L2'); // bukan langsung L1
});

it('RULE_GUESS_GUARD tidak mengubah M maupun level', function () {
    $d = engine()->evaluasi(
        mLama: 0.70, skorPemeriksaan: 0.20, levelKini: 'L2',
        sinyal: ['durasi_detik' => 12, 'median_durasi_kelas' => 300],
    );
    expect($d->kodeAturan)->toBe(AdaptationDecision::GUESS_GUARD)
        ->and($d->mSesudah)->toBe(0.70)
        ->and($d->levelSesudah)->toBe('L2');
});

// ---------- Pemilihan varian konten ----------

it('memilih varian paling spesifik yang cocok', function () {
    $varian = [
        ['id' => 1, 'level' => '*',  'modus' => '*'],
        ['id' => 2, 'level' => 'L2', 'modus' => '*'],
        ['id' => 3, 'level' => 'L2', 'modus' => 'visual'],
    ];
    expect(engine()->pilihVarian($varian, 'L2', 'visual')['id'])->toBe(3);
});

it('jatuh ke varian umum bila tidak ada yang khusus', function () {
    $varian = [['id' => 1, 'level' => '*', 'modus' => '*']];
    expect(engine()->pilihVarian($varian, 'L3', 'naratif')['id'])->toBe(1);
});

it('modus campuran cocok dengan varian mana pun', function () {
    $varian = [['id' => 7, 'level' => 'L1', 'modus' => 'simbolik']];
    expect(engine()->pilihVarian($varian, 'L1', 'campuran')['id'])->toBe(7);
});
