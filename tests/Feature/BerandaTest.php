<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('menampilkan beranda publik untuk tamu', function (): void {
    get('/')
        ->assertOk()
        ->assertSee('GEULIS', false)
        ->assertSee('Delapan kelopak. Satu putaran 45°.', false)
        ->assertSee('Masuk belajar', false)
        ->assertSee('Translasi', false)
        ->assertSee('Rozet payung geulis', false)
        ->assertSee('Identitas penelitian', false)
        ->assertSee('Penelitian Pengembangan Kapasitas (PPKap)', false)
        ->assertSee('Depi Ardian Nugraha', false)
        ->assertSee('Asesmen dan evaluasi pembelajaran matematika', false)
        ->assertSee('Dr. Eko Yulianto', false)
        ->assertSee('Kajian antropologi budaya dalam etnomatematika', false)
        ->assertSee('Satya Santika', false)
        ->assertSee('Vepi Apiati', false)
        ->assertSee('Reza Mohammad Rizqi', false)
        ->assertSee('Aufa Dzakiya Aziza', false)
        ->assertSee(route('masuk'), false);
});

it('tetap mengarahkan pengguna masuk ke rumah perannya', function (): void {
    $guru = User::factory()->guru()->create();

    actingAs($guru)->get('/')->assertRedirect('/guru');
});
