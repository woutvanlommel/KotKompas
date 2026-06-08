<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class SocialAuthController extends Controller
{
    /**
     * Providers we accept. Anything else 404s.
     */
    private const PROVIDERS = ['google'];

    public function redirect(string $provider): SymfonyRedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        $this->setRedirectUrl($provider);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Point the driver's callback at our named route (same on redirect + callback).
     */
    private function setRedirectUrl(string $provider): void
    {
        config(['services.'.$provider.'.redirect' => route('social.callback', ['provider' => $provider])]);
    }

    public function callback(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        $this->setRedirectUrl($provider);

        try {
            $oauthUser = Socialite::driver($provider)->user();
        } catch (\Throwable $e) {
            report($e);

            return redirect()->to(filament()->getPanel('dashboard')->getLoginUrl())
                ->withErrors(['social' => 'Aanmelden met '.ucfirst($provider).' is mislukt. Probeer opnieuw.']);
        }

        $user = User::where('provider', $provider)
            ->where('provider_id', $oauthUser->getId())
            ->first()
            ?? User::where('email', $oauthUser->getEmail())->first();

        if ($user) {
            // Link the social identity to an existing (or already-linked) account.
            $user->forceFill([
                'provider' => $provider,
                'provider_id' => $oauthUser->getId(),
                'avatar' => $user->avatar ?: $oauthUser->getAvatar(),
            ])->save();
        } else {
            $user = User::create([
                'name' => $oauthUser->getName() ?: $oauthUser->getNickname() ?: 'Gebruiker',
                'email' => $oauthUser->getEmail(),
                'provider' => $provider,
                'provider_id' => $oauthUser->getId(),
                'avatar' => $oauthUser->getAvatar(),
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user, remember: true);

        // No role yet → force the one-time role choice before entering the panel.
        if (! $user->hasAnyRole(['huurder', 'verhuurder'])) {
            return redirect()->route('onboarding.role');
        }

        return redirect()->to(filament()->getPanel('dashboard')->getUrl());
    }
}
