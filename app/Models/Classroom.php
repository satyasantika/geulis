<?php

namespace App\Models;

use Database\Factories\ClassroomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Kelas (rombongan belajar). `kelompok_riset` melekat di sini, bukan pada
 * siswa — sesuai desain kuasi-eksperimen kelas utuh.
 */
#[Fillable(['school_id', 'guru_id', 'nama', 'tahun_ajaran', 'kelompok_riset', 'kode_gabung', 'aktif', 'simulation_run_id'])]
class Classroom extends Model
{
    /** @use HasFactory<ClassroomFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (self $kelas): void {
            $kelas->kode_gabung ??= self::kodeGabungBaru();
        });
    }

    /**
     * Kode gabung 6 huruf besar/angka tanpa karakter yang mirip (0/O, 1/I),
     * supaya mudah dibacakan guru di depan kelas.
     */
    public static function kodeGabungBaru(): string
    {
        do {
            $kode = strtoupper(Str::random(6));
            $kode = strtr($kode, ['0' => '2', 'O' => '3', '1' => '4', 'I' => '5', 'L' => '6']);
        } while (static::query()->where('kode_gabung', $kode)->exists());

        return $kode;
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function simulationRun(): BelongsTo
    {
        return $this->belongsTo(SimulationRun::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function siswa(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments')
            ->withPivot(['level_kini', 'modus_kini', 'status'])
            ->withTimestamps()
            ->orderBy('nama');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }
}
