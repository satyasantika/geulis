<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Jejak keputusan Mesin Diferensiasi.
 *
 * Baris-baris di tabel inilah bukti empiris bahwa sistem benar-benar adaptif,
 * dan bahan mentah untuk bagian temuan pada artikel. JANGAN pernah dihapus
 * atau ditimpa; setiap keputusan menulis baris baru.
 */
class AdaptationLog extends Model
{
    protected $fillable = [
        'user_id', 'lesson_unit_id', 'kode_aturan', 'm_sebelum', 'm_sesudah',
        'skor_pemeriksaan', 'level_sebelum', 'level_sesudah', 'keputusan', 'konteks',
    ];

    protected $casts = [
        'konteks' => 'array',
        'm_sebelum' => 'float',
        'm_sesudah' => 'float',
        'skor_pemeriksaan' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lessonUnit(): BelongsTo
    {
        return $this->belongsTo(LessonUnit::class);
    }
}
