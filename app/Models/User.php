<?php

namespace App\Models;

use App\Enums\Peran;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Pengguna: siswa, guru, validator, observer, peneliti, admin.
 *
 * `username` adalah NIS untuk siswa dan NIP/inisial untuk guru; siswa masuk
 * memakai NIS + PIN 6 digit (PIN di-hash pada kolom `password`). Surel opsional
 * — sebagian besar siswa SMA tidak punya surel aktif.
 *
 * `kode_anonim` (S-001, …) dipakai pada SEMUA ekspor data; nama dan NIS
 * tidak pernah keluar dari sistem.
 */
#[Fillable(['nama', 'username', 'email', 'password', 'pin_kartu', 'school_id', 'jenis_kelamin', 'kode_anonim', 'aktif', 'terakhir_masuk_pada'])]
#[Hidden(['password', 'pin_kartu', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasName
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Panel /admin hanya untuk pengelola (admin, peneliti). Guru dan siswa
     * tidak pernah melihat Filament — layar mereka dibangun terpisah agar
     * mobile-first dan nadanya sepenuhnya di tangan kita.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->aktif && $this->punyaPeran(...Peran::pengelolaPanel());
    }

    /**
     * Benar bila pengguna memegang SALAH SATU peran yang disebut.
     * Relasi `roles` dimuat sekali lalu dipakai ulang supaya pemeriksaan
     * middleware pada tiap permintaan tidak menambah kueri.
     */
    public function punyaPeran(Peran|string ...$peran): bool
    {
        $dicari = array_map(
            fn (Peran|string $p): string => $p instanceof Peran ? $p->value : $p,
            $peran,
        );

        return $this->roles->pluck('nama')->intersect($dicari)->isNotEmpty();
    }

    /**
     * Tambahkan peran tanpa menggandakan yang sudah ada.
     */
    public function berikanPeran(Peran ...$peran): static
    {
        $ids = array_map(fn (Peran $p): int => Role::untuk($p)->getKey(), $peran);

        $this->roles()->syncWithoutDetaching($ids);
        $this->unsetRelation('roles');

        return $this;
    }

    /**
     * Tujuan setelah masuk, ditentukan dari peran. Siswa ke jalur belajar,
     * guru ke berandanya, pengelola ke panel; peran lain (validator, observer)
     * masuk lewat tautan bertanda sehingga jatuh ke beranda umum.
     */
    public function rutePulang(): string
    {
        return match (true) {
            $this->punyaPeran(Peran::Siswa) => route('siswa.jalur'),
            $this->punyaPeran(Peran::Guru) => route('guru.beranda'),
            $this->punyaPeran(...Peran::pengelolaPanel()) => url('/admin'),
            default => route('beranda'),
        };
    }

    public function getFilamentName(): string
    {
        return $this->nama;
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'enrollments')
            ->withPivot(['level_kini', 'modus_kini', 'status'])
            ->withTimestamps();
    }

    /** Kelas yang diampu (untuk guru). */
    public function kelasDiampu(): HasMany
    {
        return $this->hasMany(Classroom::class, 'guru_id');
    }

    public function consent(): HasOne
    {
        return $this->hasOne(Consent::class);
    }

    public function learningProfile(): HasOne
    {
        return $this->hasOne(LearningProfile::class);
    }

    public function placement(): HasOne
    {
        return $this->hasOne(Placement::class);
    }

    public function readinessResponses(): HasMany
    {
        return $this->hasMany(ReadinessResponse::class);
    }

    public function masteryStates(): HasMany
    {
        return $this->hasMany(MasteryState::class);
    }

    public function adaptationLogs(): HasMany
    {
        return $this->hasMany(AdaptationLog::class);
    }

    public function unitProgress(): HasMany
    {
        return $this->hasMany(LessonUnitProgress::class);
    }

    public function activityAttempts(): HasMany
    {
        return $this->hasMany(ActivityAttempt::class);
    }

    public function motifSubmissions(): HasMany
    {
        return $this->hasMany(MotifSubmission::class);
    }

    /** Enrollment aktif — posisi adaptif siswa saat ini. */
    public function enrollmentAktif(): ?Enrollment
    {
        return $this->enrollments()->where('status', 'aktif')->first();
    }

    /** Kelas aktif pertama tempat siswa terdaftar. */
    public function kelasAktif(): ?Classroom
    {
        return $this->classrooms()->wherePivot('status', 'aktif')->first();
    }

    /**
     * Kode anonim berikutnya, berurutan global (S-001, S-002, …). Global,
     * bukan per sekolah, supaya tidak pernah bentrok saat data tiga sekolah
     * digabung; sekolah dan kelas tetap menjadi kolom sendiri saat ekspor.
     */
    public static function kodeAnonimBerikutnya(string $awalan = 'S'): string
    {
        $terakhir = static::query()
            ->where('kode_anonim', 'like', $awalan.'-%')
            ->orderByDesc('kode_anonim')
            ->value('kode_anonim');

        $nomor = $terakhir === null ? 1 : ((int) substr($terakhir, strlen($awalan) + 1)) + 1;

        return sprintf('%s-%03d', $awalan, $nomor);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'pin_kartu' => 'encrypted',
            'aktif' => 'boolean',
            'terakhir_masuk_pada' => 'datetime',
        ];
    }
}
