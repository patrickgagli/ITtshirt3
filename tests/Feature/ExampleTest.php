<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_uses_the_application_landing_screen(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertViewIs('home')
            ->assertSee('ITtshirt3');
    }
}
