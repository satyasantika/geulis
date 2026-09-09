<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
#[Fillable(['nama', 'username', 'email', 'password', 'school_id', 'jenis_kelamin', 'kode_anonim', 'aktif', 'terakhir_masuk_pada'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasName
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Sementara terbuka untuk semua pengguna. Pembatasan per peran (hanya
     * admin/peneliti) dikerjakan pada Sprint 0 butir 5 bersama middleware `role:`.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
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

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'aktif' => 'boolean',
            'terakhir_masuk_pada' => 'datetime',
        ];
    }
}
