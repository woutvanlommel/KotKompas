<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class SocialAuthController extends Controller
{
    /**
     * Providers we accept. Anything else 404s.
     */
    private const PROVIDERS = ['google', 'facebook'];

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

        // Facebook-accounts aangemaakt met enkel een telefoonnummer hebben geen
        // e-mailadres; onze accounts zijn e-mail-gebaseerd, dus netjes weigeren.
        if (! $oauthUser->getEmail()) {
            return redirect()->to(filament()->getPanel('dashboard')->getLoginUrl())
                ->withErrors(['social' => 'Je '.ucfirst($provider).'-account heeft geen e-mailadres. Registreer met je e-mailadres.']);
        }

        // A soft-deleted account still holds the unique email slot — refuse a fresh
        // sign-up rather than crash on the unique constraint (and don't silently revive it).
        if (User::onlyTrashed()->where('email', $oauthUser->getEmail())->exists()) {
            return redirect()->to(filament()->getPanel('dashboard')->getLoginUrl())
                ->withErrors(['social' => 'Dit account is niet langer actief.']);
        }

        $user = User::where('provider', $provider)
            ->where('provider_id', $oauthUser->getId())
            ->first();

        if (! $user) {
            $emailMatch = User::where('email', $oauthUser->getEmail())->first();

            // Alleen koppelen aan een geverifieerd account. Een ongeverifieerd
            // account kan door iemand anders met dit adres geregistreerd zijn —
            // koppelen zou dan een account takeover zijn.
            if ($emailMatch && $emailMatch->email_verified_at === null) {
                return redirect()->to(filament()->getPanel('dashboard')->getLoginUrl())
                    ->withErrors(['social' => 'Er bestaat al een account met dit e-mailadres. Log in met je wachtwoord.']);
            }

            $user = $emailMatch;
        }

        if ($user) {
            // Link the social identity to an existing (or already-linked) account.
            // De provider heeft het e-mailadres geverifieerd, dus dat mag hier ook.
            $user->forceFill([
                'provider' => $provider,
                'provider_id' => $oauthUser->getId(),
                'avatar' => $user->avatar ?: $oauthUser->getAvatar(),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        } else {
            // Google returns first/last separately as given_name/family_name; fall back
            // to splitting the full name on whitespace.
            $raw = (array) ($oauthUser->user ?? []);
            $fullName = $oauthUser->getName() ?: $oauthUser->getNickname() ?: 'Gebruiker';

            $user = User::create([
                'name' => $raw['given_name'] ?? Str::before($fullName, ' '),
                'lastname' => $raw['family_name'] ?? (Str::contains($fullName, ' ') ? Str::after($fullName, ' ') : null),
                'email' => $oauthUser->getEmail(),
                'provider' => $provider,
                'provider_id' => $oauthUser->getId(),
                'avatar' => $oauthUser->getAvatar(),
            ]);

            // Niet via create(): email_verified_at staat bewust niet in #[Fillable],
            // dus mass assignment liet dit veld stilletjes vallen.
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate(); // prevent session fixation

        // No role yet → force the one-time role choice before entering the panel.
        if (! $user->hasAnyRole(['huurder', 'verhuurder'])) {
            return redirect()->route('onboarding.role');
        }

        return redirect()->to(filament()->getPanel('dashboard')->getUrl());
    }
}
