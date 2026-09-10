<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'readiness_item_id', 'jawaban', 'benar', 'durasi_detik'])]
class ReadinessResponse extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ReadinessItem::class, 'readiness_item_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['benar' => 'boolean'];
    }
}
