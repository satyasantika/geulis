<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Override level oleh guru. `alasan` WAJIB — data kualitatif: di titik mana
 * penilaian guru berbeda dari keputusan sistem, dan mengapa.
 */
#[Fillable(['guru_id', 'siswa_id', 'lesson_unit_id', 'level_lama', 'level_baru', 'alasan'])]
class TeacherOverride extends Model
{
    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    public function lessonUnit(): BelongsTo
    {
        return $this->belongsTo(LessonUnit::class);
    }
}
