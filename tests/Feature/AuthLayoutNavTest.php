<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLayoutNavTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_has_site_navigation(): void
    {
        $response = $this->get('/dashboard/login');

        $response->assertOk();
        $response->assertSee(route('rooms.index'), false); // Koten
        $response->assertSee(route('faq'), false);         // FAQ
        $response->assertSee(route('contact'), false);     // Contact
    }

    public function test_register_page_has_site_navigation(): void
    {
        $response = $this->get('/dashboard/register');

        $response->assertOk();
        $response->assertSee(route('rooms.index'), false);
        $response->assertSee(route('faq'), false);
    }

    public function test_contact_page_has_site_navigation(): void
    {
        $response = $this->get(route('contact'));

        $response->assertOk();
        $response->assertSee(route('rooms.index'), false);
        $response->assertSee(route('faq'), false);
    }
}
