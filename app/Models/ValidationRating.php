<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['expert_validation_id', 'validation_item_id', 'skor', 'saran'])]
class ValidationRating extends Model
{
    public function validation(): BelongsTo
    {
        return $this->belongsTo(ExpertValidation::class, 'expert_validation_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ValidationItem::class, 'validation_item_id');
    }
}
