<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Guru\PinCardController;
use App\Http\Controllers\Siswa\ConsentController;
use App\Livewire\Guru\Beranda as GuruBeranda;
use App\Livewire\Guru\DetailKelas;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute GEULIS
|--------------------------------------------------------------------------
| Nama rute mengikuti kode layar cetak biru (S-xx, G-xx, …); peta lengkapnya
| di docs/spesifikasi/peta-rute-web.php. Rute ditambahkan saat layarnya dibuat.
*/

// ---------- Publik ----------
Route::get('/', fn () => auth()->check()
    ? redirect(auth()->user()->rutePulang())
    : redirect()->route('masuk'))->name('beranda');

Route::middleware('guest')->group(function (): void {
    Route::get('/masuk', [LoginController::class, 'form'])->name('masuk');            // S-01
    Route::post('/masuk', [LoginController::class, 'proses'])->name('masuk.proses');
});

Route::post('/keluar', [LoginController::class, 'keluar'])->middleware('auth')->name('keluar');

// Nama rute `login` masih dicari beberapa paket; arahkan ke S-01.
Route::redirect('/login', '/masuk')->name('login');

// ---------- Siswa ----------
Route::middleware(['auth', 'role:siswa'])->prefix('belajar')->name('siswa.')->group(function (): void {
    Route::get('/persetujuan', [ConsentController::class, 'form'])->name('persetujuan');          // S-02
    Route::post('/persetujuan', [ConsentController::class, 'simpan'])->name('persetujuan.simpan');

    Route::middleware('persetujuan')->group(function (): void {
        Route::view('/', 'siswa.jalur')->name('jalur');                                          // S-04 (kerangka)
    });
});

// ---------- Guru ----------
Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function (): void {
    Route::get('/', GuruBeranda::class)->name('beranda');                                        // G-00
    Route::get('/kelas/{classroom}', DetailKelas::class)->name('kelas');                         // G-00 detail
    Route::get('/kartu-pin/{classroom}', [PinCardController::class, 'cetak'])->name('kartu-pin'); // G-05
});
