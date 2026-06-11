<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_is_rate_limited(): void
    {
        // 5 per minuut toegestaan; de 6e moet een 429 geven.
        for ($i = 0; $i < 5; $i++) {
            $this->post('/contact', [])->assertStatus(302);
        }

        $this->post('/contact', [])->assertStatus(429);
    }
}
