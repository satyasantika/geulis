<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['observation_id', 'observation_item_id', 'skor'])]
class ObservationRecord extends Model
{
    public function observation(): BelongsTo
    {
        return $this->belongsTo(Observation::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ObservationItem::class, 'observation_item_id');
    }
}
