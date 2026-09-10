<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    Route::middleware(['web', 'auth', 'role:guru'])->get('/uji/guru', fn () => 'ok-guru');
    Route::middleware(['web', 'auth', 'role:admin,peneliti'])->get('/uji/pengelola', fn () => 'ok-pengelola');
});

it('redirects guests to the login form', function (): void {
    get('/uji/guru')->assertRedirect(route('masuk'));
});

it('forbids a user without the required role', function (): void {
    actingAs(User::factory()->siswa()->create())
        ->get('/uji/guru')
        ->assertForbidden();
});

it('forbids a user with no role at all', function (): void {
    actingAs(User::factory()->create())
        ->get('/uji/guru')
        ->assertForbidden();
});

it('allows a user with the required role', function (): void {
    actingAs(User::factory()->guru()->create())
        ->get('/uji/guru')
        ->assertOk()
        ->assertSee('ok-guru');
});

it('allows any one of several listed roles', function (): void {
    actingAs(User::factory()->peneliti()->create())
        ->get('/uji/pengelola')
        ->assertOk();

    actingAs(User::factory()->guru()->create())
        ->get('/uji/pengelola')
        ->assertForbidden();
});

it('protects the student and teacher areas', function (): void {
    $siswa = User::factory()->siswa()->create();
    $guru = User::factory()->guru()->create();

    actingAs($siswa)->get('/guru')->assertForbidden();
    actingAs($guru)->get('/belajar')->assertForbidden();
    actingAs($guru)->get('/guru')->assertOk();
});
