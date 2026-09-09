<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    protected $fillable = ['urutan', 'judul', 'materi', 'artefak_utama', 'fokus_ct', 'capaian_pembelajaran', 'terbit'];

    protected $casts = ['terbit' => 'boolean'];

    public function lessonUnits(): HasMany
    {
        return $this->hasMany(LessonUnit::class)->orderBy('urutan');
    }
}
