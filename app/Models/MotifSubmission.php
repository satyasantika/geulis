<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Kiriman Motif Builder — bukti proses CT (algoritma, abstraksi, dekomposisi,
 * pengenalan pola). Skor mentah disimpan apa adanya untuk analisis ulang.
 */
#[Fillable(['user_id', 'activity_id', 'percobaan_ke', 'urutan_perintah', 'skor_kemiripan', 'langkah_siswa', 'langkah_minimum', 'efisiensi', 'motif_dasar_ditandai', 'lolos', 'cuplikan_svg'])]
class MotifSubmission extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'urutan_perintah' => 'array',
            'motif_dasar_ditandai' => 'array',
            'skor_kemiripan' => 'float',
            'efisiensi' => 'float',
            'lolos' => 'boolean',
        ];
    }
}
