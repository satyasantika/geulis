<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Guru\PinCardController;
use App\Http\Controllers\Produk\ProductFileController;
use App\Http\Controllers\Riset\EksporController;
use App\Http\Controllers\Riset\SaranController;
use App\Http\Controllers\Siswa\ConsentController;
use App\Livewire\Angket\AngketRespons;
use App\Livewire\Guru\Beranda as GuruBeranda;
use App\Livewire\Guru\DetailKelas;
use App\Livewire\Guru\PapanKelas;
use App\Livewire\Guru\Pendampingan;
use App\Livewire\Guru\PenilaianRubrik;
use App\Livewire\Guru\PenilaianUraian;
use App\Livewire\Guru\RaporSiswa;
use App\Livewire\Observasi\LembarObservasi;
use App\Livewire\Peneliti\Analitik;
use App\Livewire\Peneliti\Ekspor;
use App\Livewire\Peneliti\Kelengkapan;
use App\Livewire\Peneliti\Validasi as PenelitiValidasi;
use App\Livewire\Siswa\AsesmenAwal;
use App\Livewire\Siswa\Jalur;
use App\Livewire\Siswa\Kemajuan;
use App\Livewire\Siswa\MotifBuilder;
use App\Livewire\Siswa\Pertemuan;
use App\Livewire\Siswa\Produk;
use App\Livewire\Siswa\TesCt;
use App\Livewire\Siswa\Unit;
use App\Livewire\Validasi\LembarValidasi;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute GEULIS
|--------------------------------------------------------------------------
| Nama rute mengikuti kode layar cetak biru (S-xx, G-xx, …); peta lengkapnya
| di docs/spesifikasi/peta-rute-web.php. Rute ditambahkan saat layarnya dibuat.
*/

// ---------- Publik ----------
Route::get('/', function () {
    if (auth()->check()) {
        return redirect(auth()->user()->rutePulang());
    }

    return view('beranda');
})->name('beranda');

Route::middleware('guest')->group(function (): void {
    Route::get('/masuk', [LoginController::class, 'form'])->name('masuk');            // S-01
    Route::post('/masuk', [LoginController::class, 'proses'])->name('masuk.proses');
});

Route::post('/keluar', [LoginController::class, 'keluar'])->middleware('auth')->name('keluar');

// Nama rute `login` masih dicari beberapa paket; arahkan ke S-01.
Route::redirect('/login', '/masuk')->name('login');

// Validator masuk lewat tautan bertanda — tanpa akun.                                             // V-01
Route::get('/validasi/{token}', LembarValidasi::class)->middleware('signed')->name('validasi.form');

// ---------- Siswa ----------
Route::middleware(['auth', 'role:siswa'])->prefix('belajar')->name('siswa.')->group(function (): void {
    Route::get('/persetujuan', [ConsentController::class, 'form'])->name('persetujuan');          // S-02
    Route::post('/persetujuan', [ConsentController::class, 'simpan'])->name('persetujuan.simpan');

    Route::middleware('persetujuan')->group(function (): void {
        Route::get('/', Jalur::class)->name('jalur');                                             // S-04
        Route::get('/asesmen-awal', AsesmenAwal::class)->name('asesmen');                          // S-03
        Route::get('/pertemuan/{meeting}', Pertemuan::class)->name('pertemuan');                   // S-05
        Route::get('/unit/{lessonUnit}', Unit::class)->name('unit');                               // S-05/S-06/S-08
        Route::get('/motif/{activity}', MotifBuilder::class)->name('motif');                       // S-07
        Route::get('/tes-ct/{ctTest}', TesCt::class)->name('tes-ct');                              // S-11
        Route::get('/kemajuan', Kemajuan::class)->name('kemajuan');                                // S-09
        Route::get('/produk/{meeting}', Produk::class)->name('produk');                            // S-10
        Route::get('/angket', AngketRespons::class)->defaults('sasaran', 'siswa')->name('angket');  // angket respons siswa
    });
});

// Berkas produk siswa: privat, diotorisasi di controller (pemilik / guru kelas / peneliti / admin).
Route::get('/produk/{product}/berkas', ProductFileController::class)->middleware('auth')->name('produk.berkas');

// ---------- Guru ----------
Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function (): void {
    Route::get('/', GuruBeranda::class)->name('beranda');                                        // G-00
    Route::get('/kelas/{classroom}', DetailKelas::class)->name('kelas');                         // G-00 detail
    Route::get('/kartu-pin/{classroom}', [PinCardController::class, 'cetak'])->name('kartu-pin'); // G-05
    Route::get('/penilaian-uraian', PenilaianUraian::class)->name('penilaian-uraian');            // G-03 (uraian CT)
    Route::get('/pendampingan', Pendampingan::class)->name('pendampingan');                        // G-04
    Route::get('/kelas/{classroom}/papan', PapanKelas::class)->name('papan');                     // G-01
    Route::get('/siswa/{user}', RaporSiswa::class)->name('siswa');                                // G-02
    Route::get('/penilaian/{meeting}', PenilaianRubrik::class)->name('penilaian');                // G-03
    Route::get('/angket', AngketRespons::class)->defaults('sasaran', 'guru')->name('angket');       // angket respons guru
});

// ---------- Observer ----------
Route::middleware(['auth', 'role:observer'])->prefix('observasi')->name('observasi.')->group(function (): void {
    Route::get('/', LembarObservasi::class)->name('form');                                        // O-01
});

// ---------- Peneliti ----------
Route::middleware(['auth', 'role:peneliti'])->prefix('riset')->name('riset.')->group(function (): void {
    Route::get('/kelengkapan', Kelengkapan::class)->name('kelengkapan');                          // P-01
    Route::get('/analitik', Analitik::class)->name('analitik');                                   // P-02
    Route::get('/validasi', PenelitiValidasi::class)->name('validasi');                           // V-02
    Route::get('/validasi/saran.csv', SaranController::class)->name('saran');
    Route::get('/ekspor', Ekspor::class)->name('ekspor');                                         // P-03
    Route::get('/ekspor/{dataExport}', EksporController::class)->name('ekspor.unduh');
});
