<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Profil belajar: skor kecenderungan tiga modus (0–100), BUKAN label kaku.
 * `jawaban_mentah` menyimpan seluruh jawaban angket (termasuk draf) untuk
 * analisis ulang dan supaya siswa yang putus koneksi tidak mengulang.
 */
#[Fillable(['user_id', 'skor_visual', 'skor_simbolik', 'skor_naratif', 'modus_utama', 'artefak_pilihan', 'jawaban_mentah'])]
class LearningProfile extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['jawaban_mentah' => 'array'];
    }
}
