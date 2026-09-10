<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tamu melihat beranda publik; rincian per peran diuji di Auth/LoginTest.
     */
    public function test_the_application_responds_on_the_root_path(): void
    {
        $this->get('/')->assertOk()->assertSee('GEULIS');
    }
}
