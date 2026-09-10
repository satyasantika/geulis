<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'rubric_criteria_id', 'penilai_id', 'tingkat', 'umpan_balik'])]
class RubricScore extends Model
{
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(RubricCriteria::class, 'rubric_criteria_id');
    }

    public function penilai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penilai_id');
    }
}
