<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['rubric_id', 'kriteria', 'bobot', 'deskriptor'])]
class RubricCriteria extends Model
{
    protected $table = 'rubric_criteria';

    public function rubric(): BelongsTo
    {
        return $this->belongsTo(Rubric::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['deskriptor' => 'array'];
    }
}
