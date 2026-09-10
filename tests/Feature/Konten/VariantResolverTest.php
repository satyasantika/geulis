<?php

use App\Models\Classroom;
use App\Models\ContentVariant;
use App\Models\LessonUnit;
use App\Models\User;
use App\Services\Konten\VariantResolver;

function siswaDenganLevel(string $level, string $modus = 'visual'): User
{
    $siswa = User::factory()->siswa()->create();
    Classroom::factory()->create()->enrollments()->create(['user_id' => $siswa->id, 'level_kini' => $level, 'modus_kini' => $modus]);

    return $siswa;
}

it('serves different variants to L1 and L3 students on the same unit', function (): void {
    $unit = LessonUnit::factory()->create();
    $l1 = ContentVariant::factory()->for($unit)->untuk('L1', '*')->create(['judul' => 'Lipatan kain terbimbing']);
    $l2Visual = ContentVariant::factory()->for($unit)->untuk('L2', 'visual')->create(['judul' => 'Cermin diagonal']);
    $l2Semua = ContentVariant::factory()->for($unit)->untuk('L2', '*')->create(['judul' => 'Aturan (x,y) → (y,x)']);
    $l3 = ContentVariant::factory()->for($unit)->untuk('L3', '*')->create(['judul' => 'Komposisi refleksi']);

    $resolver = app(VariantResolver::class);

    expect($resolver->untuk(siswaDenganLevel('L1'), $unit)->is($l1))->toBeTrue()
        ->and($resolver->untuk(siswaDenganLevel('L3'), $unit)->is($l3))->toBeTrue()
        ->and($resolver->untuk(siswaDenganLevel('L2', 'visual'), $unit)->is($l2Visual))->toBeTrue()
        ->and($resolver->untuk(siswaDenganLevel('L2', 'simbolik'), $unit)->is($l2Semua))->toBeTrue();
});

it('falls back to the wildcard variant and ignores drafts', function (): void {
    $unit = LessonUnit::factory()->create();
    $umum = ContentVariant::factory()->for($unit)->untuk('*', '*')->create();
    ContentVariant::factory()->for($unit)->untuk('L1', '*')->create(['status' => 'draf']);

    $resolver = app(VariantResolver::class);

    expect($resolver->untuk(siswaDenganLevel('L1'), $unit)->is($umum))->toBeTrue()
        ->and($resolver->untuk(siswaDenganLevel('L2'), LessonUnit::factory()->create()))->toBeNull();
});
