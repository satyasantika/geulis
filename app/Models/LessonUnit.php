<?php

namespace App\Models;

use Database\Factories\LessonUnitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LessonUnit extends Model
{
    /** @use HasFactory<LessonUnitFactory> */
    use HasFactory;

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

    public function progress(): HasMany
    {
        return $this->hasMany(LessonUnitProgress::class);
    }

    public function label(): string
    {
        return "Bagian {$this->urutan} — {$this->judul}";
    }
}
