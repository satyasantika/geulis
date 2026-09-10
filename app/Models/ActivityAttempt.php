<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'activity_id', 'percobaan_ke', 'jawaban', 'skor', 'durasi_detik', 'mulai_pada', 'selesai_pada', 'dugaan_menebak'])]
class ActivityAttempt extends Model
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
            'jawaban' => 'array',
            'skor' => 'float',
            'mulai_pada' => 'datetime',
            'selesai_pada' => 'datetime',
            'dugaan_menebak' => 'boolean',
        ];
    }
}
