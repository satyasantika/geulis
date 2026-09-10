<?php

namespace App\Models;

use Database\Factories\ActivityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Aktivitas pada satu unit: kuis (latihan/pemeriksaan), geogebra,
 * motif_builder, unggah, refleksi. `konfigurasi` JSON menyimpan butir soal
 * atau motif sasaran; `level` '*' berlaku untuk semua level.
 */
#[Fillable(['lesson_unit_id', 'tipe', 'judul', 'konfigurasi', 'skor_maks', 'level'])]
class Activity extends Model
{
    /** @use HasFactory<ActivityFactory> */
    use HasFactory;

    public function lessonUnit(): BelongsTo
    {
        return $this->belongsTo(LessonUnit::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ActivityAttempt::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['konfigurasi' => 'array'];
    }
}
