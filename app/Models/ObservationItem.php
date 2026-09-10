<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['urutan', 'pernyataan', 'aspek'])]
class ObservationItem extends Model {}
