<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Lembar observasi keterlaksanaan satu pertemuan di satu kelas. */
#[Fillable(['observer_id', 'classroom_id', 'meeting_id', 'tanggal', 'catatan_lapangan'])]
class Observation extends Model
{
    public function observer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'observer_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function records(): HasMany
    {
        return $this->hasMany(ObservationRecord::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }
}
