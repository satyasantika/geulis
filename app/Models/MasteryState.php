<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasteryState extends Model
{
    protected $fillable = [
        'user_id', 'lesson_unit_id', 'nilai_m', 'level_saat_itu',
        'iterasi_remedial', 'perlu_pendampingan',
    ];

    protected $casts = [
        'nilai_m' => 'float',
        'perlu_pendampingan' => 'boolean',
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
