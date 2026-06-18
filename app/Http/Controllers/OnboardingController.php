<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function showRole(): View|RedirectResponse
    {
        // Already has a role → nothing to choose.
        if (auth()->user()->hasAnyRole(['huurder', 'verhuurder'])) {
            return redirect()->intended(filament()->getPanel('dashboard')->getUrl());
        }

        return view('onboarding.role');
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', 'in:huurder,verhuurder'],
        ]);

        $user = auth()->user();

        // Guard against re-assigning if a role was set in the meantime.
        if (! $user->hasAnyRole(['huurder', 'verhuurder'])) {
            $user->assignRole($data['role']);
        }

        return redirect()->intended(filament()->getPanel('dashboard')->getUrl());
    }
}
