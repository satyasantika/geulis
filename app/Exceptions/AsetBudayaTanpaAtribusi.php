<?php

namespace App\Exceptions;

use InvalidArgumentException;

/**
 * Dilempar model CulturalAsset bila atribusi/izin tidak lengkap (aturan #6).
 */
class AsetBudayaTanpaAtribusi extends InvalidArgumentException {}
