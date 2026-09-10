<?php

namespace App\Models;

use Database\Factories\MeetingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Meeting extends Model
{
    /** @use HasFactory<MeetingFactory> */
    use HasFactory;

    protected $fillable = ['urutan', 'judul', 'materi', 'artefak_utama', 'fokus_ct', 'capaian_pembelajaran', 'terbit'];

    protected $casts = ['terbit' => 'boolean'];

    public function lessonUnits(): HasMany
    {
        return $this->hasMany(LessonUnit::class)->orderBy('urutan');
    }

    public function contentVariants(): HasManyThrough
    {
        return $this->hasManyThrough(ContentVariant::class, LessonUnit::class);
    }
}
