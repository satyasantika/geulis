<?php

use Auth\LoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Peta rute GEULIS
|--------------------------------------------------------------------------
| Nama rute mengikuti kode layar pada cetak biru (S-xx, G-xx, V-xx, O-xx,
| P-xx, A-xx) supaya wireframe, dokumen, dan kode tetap sejalan.
|
| Controller belum diisi - berkas ini adalah peta, bukan implementasi.
*/

// ---------- Publik ----------
Route::view('/', 'beranda')->name('beranda');
Route::get('/masuk', [LoginController::class, 'form'])->name('masuk');           // S-01
Route::post('/masuk', [LoginController::class, 'proses']);
Route::post('/keluar', [LoginController::class, 'keluar'])->name('keluar');
Route::get('/gabung/{kode}', [ClassroomJoinController::class, 'form'])->name('gabung');

// Validator masuk lewat tautan bertanda - tanpa perlu membuat akun.
Route::get('/validasi/{token}', [ExpertValidationController::class, 'form'])
    ->name('validasi.form')->middleware('signed');                                    // V-01
Route::post('/validasi/{token}', [ExpertValidationController::class, 'simpan'])
    ->middleware('signed');

// ---------- Siswa ----------
Route::middleware(['auth', 'role:siswa'])->prefix('belajar')->name('siswa.')->group(function () {
    Route::get('/persetujuan', [ConsentController::class, 'form'])->name('persetujuan');       // S-02
    Route::post('/persetujuan', [ConsentController::class, 'simpan']);

    Route::get('/asesmen-awal', [OnboardingController::class, 'mulai'])->name('asesmen');      // S-03
    Route::get('/asesmen-awal/kesiapan', [ReadinessController::class, 'form'])->name('kesiapan');
    Route::post('/asesmen-awal/kesiapan', [ReadinessController::class, 'simpan']);
    Route::get('/asesmen-awal/profil', [LearningProfileController::class, 'form'])->name('profil');
    Route::post('/asesmen-awal/profil', [LearningProfileController::class, 'simpan']);
    // -> memicu DifferentiationEngine::tempatkan()

    Route::get('/', [LearningPathController::class, 'index'])->name('jalur');                  // S-04
    Route::get('/pertemuan/{meeting}', [MeetingController::class, 'show'])->name('pertemuan'); // S-05
    Route::get('/unit/{lessonUnit}', [LessonUnitController::class, 'show'])->name('unit');     // S-05/S-06

    Route::post('/aktivitas/{activity}/kirim', [ActivityController::class, 'kirim'])->name('aktivitas.kirim');
    // -> untuk unit bertipe 'pemeriksaan': memicu DifferentiationEngine::evaluasi()

    Route::get('/motif/{activity}', [MotifBuilderController::class, 'show'])->name('motif');   // S-07
    Route::post('/motif/{activity}', [MotifBuilderController::class, 'nilai']);

    Route::get('/kemajuan', [ProgressController::class, 'index'])->name('kemajuan');           // S-09
    Route::get('/produk/{meeting}', [ProductController::class, 'form'])->name('produk');       // S-10
    Route::post('/produk/{meeting}', [ProductController::class, 'kirim']);

    Route::get('/tes-ct/{ctTest}', [CtTestController::class, 'mulai'])->name('tes-ct');        // S-11
    Route::post('/tes-ct/{ctTest}', [CtTestController::class, 'kirim']);
});

// ---------- Guru ----------
Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/kelas/{classroom}', [TeacherBoardController::class, 'show'])->name('kelas');  // G-01
    Route::get('/siswa/{user}', [TeacherStudentController::class, 'show'])->name('siswa');     // G-02
    Route::post('/siswa/{user}/override', [TeacherOverrideController::class, 'simpan'])        // G-02
        ->name('override');  // alasan WAJIB diisi
    Route::get('/penilaian/{meeting}', [RubricGradingController::class, 'index'])->name('penilaian'); // G-03
    Route::post('/penilaian/{product}', [RubricGradingController::class, 'simpan']);
    Route::get('/pendampingan', [InterventionController::class, 'index'])->name('pendampingan'); // G-04
    Route::get('/kartu-pin/{classroom}', [PinCardController::class, 'cetak'])->name('kartu-pin'); // G-05
});

// ---------- Observer ----------
Route::middleware(['auth', 'role:observer'])->prefix('observasi')->name('observasi.')->group(function () {
    Route::get('/{classroom}/{meeting}', [ObservationController::class, 'form'])->name('form'); // O-01
    Route::post('/{classroom}/{meeting}', [ObservationController::class, 'simpan']);
});

// ---------- Peneliti ----------
Route::middleware(['auth', 'role:peneliti'])->prefix('riset')->name('riset.')->group(function () {
    Route::get('/kelengkapan', [DataCompletenessController::class, 'index'])->name('kelengkapan'); // P-01
    Route::get('/analitik', [ResearchAnalyticsController::class, 'index'])->name('analitik');      // P-02
    Route::get('/validasi/rekap', [AikenReportController::class, 'index'])->name('aiken');         // V-02
    Route::post('/validasi/kirim', [ExpertInviteController::class, 'kirim'])->name('validasi.kirim');
    Route::get('/ekspor', [DataExportController::class, 'form'])->name('ekspor');                  // P-03
    Route::post('/ekspor', [DataExportController::class, 'proses']);   // selalu memakai kode anonim
});

// ---------- Admin ----------
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('konten', ContentVariantController::class);                       // A-01
    Route::resource('aset-budaya', CulturalAssetController::class);                   // A-01
    Route::resource('pengguna', UserController::class);                               // A-02
    Route::resource('sekolah', SchoolController::class);                              // A-02
    Route::resource('kelas', ClassroomController::class);                             // A-02
    Route::get('/parameter', [AdaptationSettingController::class, 'form'])->name('parameter'); // A-03
    Route::post('/parameter', [AdaptationSettingController::class, 'simpan']);
    // Mengubah parameter SETELAH validasi ahli membatalkan keabsahan hasil validasi.
    // Kunci layar ini mulai awal Bulan 9 (feature freeze).
});
