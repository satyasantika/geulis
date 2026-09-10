<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'lesson_unit_id', 'status', 'selesai_pada'])]
class LessonUnitProgress extends Model
{
    protected $table = 'lesson_unit_progress';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lessonUnit(): BelongsTo
    {
        return $this->belongsTo(LessonUnit::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['selesai_pada' => 'datetime'];
    }
}
