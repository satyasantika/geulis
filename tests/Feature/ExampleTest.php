<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Beranda mengarahkan tamu ke S-01; rincian per peran diuji di Auth/LoginTest.
     */
    public function test_the_application_responds_on_the_root_path(): void
    {
        $this->get('/')->assertRedirect('/masuk');
    }
}
