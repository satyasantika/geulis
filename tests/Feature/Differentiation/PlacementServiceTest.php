<?php

use App\Models\Classroom;
use App\Models\ReadinessItem;
use App\Models\User;
use App\Services\Differentiation\PlacementService;

function siswaDenganKesiapan(int $benar, ?Classroom $kelas = null): User
{
    $butir = ReadinessItem::query()->count() >= 15
        ? ReadinessItem::query()->orderBy('urutan')->get()
        : ReadinessItem::factory()->count(15)->create();

    $siswa = User::factory()->siswa()->create();
    if ($kelas) {
        $kelas->enrollments()->create(['user_id' => $siswa->id]);
    }

    foreach ($butir as $i => $b) {
        $siswa->readinessResponses()->create([
            'readiness_item_id' => $b->id,
            'jawaban' => $i < $benar ? $b->kunci : ($b->kunci + 1) % 4,
            'benar' => $i < $benar,
        ]);
    }

    return $siswa;
}

function profilVisual(): array
{
    return array_map(fn (array $b) => $b['modus'] === 'visual' ? 4 : 2, config('angket.profil'));
}

function minatAnyaman(): array
{
    return array_map(fn (array $b) => $b['artefak'] === 'anyaman' ? 4 : 1, config('angket.minat'));
}

it('places two students with different readiness scores at different levels with a readable explanation', function (): void {
    $profilVisual = profilVisual();
    $minatAnyaman = minatAnyaman();
    $kelas = Classroom::factory()->create();
    $rendah = siswaDenganKesiapan(6, $kelas);   // 40 → L1
    $tinggi = siswaDenganKesiapan(13, $kelas);  // 87 → L3

    $service = app(PlacementService::class);
    $p1 = $service->tempatkan($rendah, $profilVisual, $minatAnyaman);
    $p2 = $service->tempatkan($tinggi, $profilVisual, $minatAnyaman);

    expect($p1->level_awal)->toBe('L1')
        ->and($p1->skor_readiness)->toBe(40)
        ->and($p2->level_awal)->toBe('L3')
        ->and($p2->skor_readiness)->toBe(87)
        ->and($p1->modus)->toBe('visual')
        ->and($p1->artefak_utama)->toBe('anyaman')
        ->and($p1->penjelasan['aturan_level'])->toBe('R = 40; ambang L2 = 60, L3 = 80')
        ->and($p1->penjelasan['parameter']['alpha'])->toBe(0.4)
        ->and($p1->penjelasan['kesiapan_per_prasyarat'])->toBeArray()
        ->and($p1->penjelasan['minat']['anyaman'])->toBe(8);

    expect($rendah->learningProfile->skor_visual)->toBe(100)
        ->and($rendah->learningProfile->skor_simbolik)->toBe(33)
        ->and($rendah->learningProfile->artefak_pilihan)->toBe('anyaman')
        ->and($rendah->enrollments()->first()->level_kini)->toBe('L1')
        ->and($rendah->enrollments()->first()->modus_kini)->toBe('visual')
        ->and($tinggi->enrollments()->first()->level_kini)->toBe('L3');
});

it('marks a mixed mode when two tendencies are close', function (): void {
    $minatAnyaman = minatAnyaman();
    $siswa = siswaDenganKesiapan(10);
    $campuran = array_map(fn (array $b) => $b['modus'] === 'naratif' ? 1 : 4, config('angket.profil'));

    $placement = app(PlacementService::class)->tempatkan($siswa, $campuran, $minatAnyaman);

    expect($placement->modus)->toBe('campuran')
        ->and($placement->level_awal)->toBe('L2');
});
