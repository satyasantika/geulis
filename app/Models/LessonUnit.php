<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LessonUnit extends Model
{
    protected $fillable = ['meeting_id', 'urutan', 'judul', 'tipe', 'memicu_adaptasi'];

    protected $casts = ['memicu_adaptasi' => 'boolean'];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function contentVariants(): HasMany
    {
        return $this->hasMany(ContentVariant::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
}
