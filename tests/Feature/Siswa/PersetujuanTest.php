<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

it('sends a student without a consent record to S-02 first', function (): void {
    $siswa = User::factory()->siswa()->create();

    actingAs($siswa)->get('/belajar')->assertRedirect(route('siswa.persetujuan'));
    actingAs($siswa)->get('/belajar/persetujuan')->assertOk()->assertSee('Yang TIDAK dikumpulkan');
});

it('records agreement and opens the learning path', function (): void {
    $siswa = User::factory()->siswa()->create();

    actingAs($siswa)->post('/belajar/persetujuan', ['setuju' => 'ya'])
        ->assertRedirect(route('siswa.jalur'));

    expect($siswa->consent->setuju_data_penelitian)->toBeTrue()
        ->and($siswa->consent->disetujui_pada)->not->toBeNull();

    actingAs($siswa)->get('/belajar')->assertOk();
});

it('still lets a student who declines keep learning', function (): void {
    $siswa = User::factory()->siswa()->create();

    actingAs($siswa)->post('/belajar/persetujuan', ['setuju' => 'tidak'])
        ->assertRedirect(route('siswa.jalur'));

    expect($siswa->consent->setuju_data_penelitian)->toBeFalse();

    actingAs($siswa)->get('/belajar')->assertOk();
});

it('requires a choice', function (): void {
    $siswa = User::factory()->siswa()->create();

    actingAs($siswa)->post('/belajar/persetujuan', [])
        ->assertSessionHasErrors(['setuju' => 'Pilih salah satu dulu, ya.']);

    expect($siswa->consent)->toBeNull();
});

it('lets a student change their answer later', function (): void {
    $siswa = User::factory()->siswa()->create();

    actingAs($siswa)->post('/belajar/persetujuan', ['setuju' => 'ya']);
    actingAs($siswa)->post('/belajar/persetujuan', ['setuju' => 'tidak']);

    expect($siswa->consent()->count())->toBe(1)
        ->and($siswa->fresh()->consent->setuju_data_penelitian)->toBeFalse();
});
