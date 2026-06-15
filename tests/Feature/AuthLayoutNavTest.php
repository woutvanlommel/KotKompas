<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLayoutNavTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_links_back_to_home_and_koten(): void
    {
        $response = $this->get('/dashboard/login');

        $response->assertOk();
        $response->assertSee('Koten', false);
        $response->assertSee(route('rooms.index'), false); // Koten escape link
        $response->assertSee('Home', false);
    }

    public function test_register_page_links_back_to_koten(): void
    {
        $response = $this->get('/dashboard/register');

        $response->assertOk();
        $response->assertSee(route('rooms.index'), false);
    }
}
