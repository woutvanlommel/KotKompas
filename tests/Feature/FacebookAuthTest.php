<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as OAuthUser;
use Mockery;
use Tests\TestCase;

class FacebookAuthTest extends TestCase
{
    use RefreshDatabase;

    private function fakeOauthUser(?string $email): OAuthUser
    {
        $oauthUser = new OAuthUser;
        $oauthUser->map([
            'id' => 'fb-123',
            'name' => 'Test Persoon',
            'email' => $email,
            'avatar' => 'https://example.com/avatar.jpg',
        ]);

        return $oauthUser;
    }

    private function mockSocialite(OAuthUser $oauthUser): void
    {
        $driver = Mockery::mock();
        $driver->shouldReceive('user')->andReturn($oauthUser);
        Socialite::shouldReceive('driver')->with('facebook')->andReturn($driver);
    }

    public function test_facebook_redirect_points_to_facebook(): void
    {
        config(['services.facebook.client_id' => 'test-id', 'services.facebook.client_secret' => 'test-secret']);

        $response = $this->get('/auth/facebook/redirect');

        $response->assertRedirect();
        $this->assertStringContainsString('facebook.com', $response->headers->get('Location'));
        $this->assertStringContainsString('client_id=test-id', $response->headers->get('Location'));
    }

    public function test_unknown_provider_returns_404(): void
    {
        $this->get('/auth/tiktok/redirect')->assertNotFound();
    }

    public function test_facebook_callback_creates_user_and_starts_onboarding(): void
    {
        $this->mockSocialite($this->fakeOauthUser('fb@example.com'));

        $response = $this->get('/auth/facebook/callback?code=fake');

        $response->assertRedirect(route('onboarding.role'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'fb@example.com',
            'provider' => 'facebook',
            'provider_id' => 'fb-123',
        ]);
    }

    public function test_facebook_account_without_email_is_refused(): void
    {
        $this->mockSocialite($this->fakeOauthUser(null));

        $response = $this->get('/auth/facebook/callback?code=fake');

        $response->assertRedirect();
        $response->assertSessionHasErrors('social');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }
}
