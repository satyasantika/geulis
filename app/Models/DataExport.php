<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Riwayat ekspor data — selalu dianonimkan. */
#[Fillable(['dibuat_oleh', 'jenis', 'parameter', 'berkas', 'dianonimkan'])]
class DataExport extends Model
{
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['parameter' => 'array', 'dianonimkan' => 'boolean'];
    }
}
