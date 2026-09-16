<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Satu sesi simulasi sementara (kelas + akun) yang dipicu admin.
 * Tidak punya jadwal: dibuat saat dibutuhkan, dihapus setelah selesai.
 */
#[Fillable(['school_id', 'created_by', 'jumlah_kelas', 'jumlah_guru', 'jumlah_siswa', 'layar_token'])]
class SimulationRun extends Model
{
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(SimulationLoginToken::class)->orderBy('urutan');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    public function tokenBerikutnya(): ?SimulationLoginToken
    {
        return $this->tokens()->whereNull('diklaim_pada')->orderBy('urutan')->first();
    }

    public function tautanLayar(): string
    {
        return route('simulasi.layar', $this->layar_token);
    }
}
