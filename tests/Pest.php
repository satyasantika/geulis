<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Pest — GEULIS
|--------------------------------------------------------------------------
| Uji di tests/Feature memakai TestCase Laravel dan basis data segar
| (sqlite in-memory, lihat phpunit.xml). Uji di tests/Unit adalah logika
| murni tanpa kerangka: Mesin Diferensiasi, MotifScorer, penghitung riset.
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');
