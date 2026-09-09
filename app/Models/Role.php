<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Peran: siswa | guru | validator | observer | peneliti | admin.
 */
class Role extends Model
{
    protected $fillable = ['nama', 'label'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
