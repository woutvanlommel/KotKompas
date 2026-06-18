@props(['room', 'canView' => false, 'unlockCost' => 1])

@php
    $landlord = $room->building?->landlord;
    $user = auth()->user();
    $isOwnListing = $user && $landlord && $user->id === $landlord->id;
@endphp

@if ($landlord && ! $isOwnListing)
    @php
        $initials = collect(explode(' ', trim((string) $landlord->full_name)))
            ->filter()
            ->take(2)
            ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
            ->implode('');

        $isHuurder = $user?->hasRole('huurder') ?? false;
        $balance = ($isHuurder && ! $canView)
            ? app(\App\Services\CreditService::class)->balance($user)
            : null;
        $hasEnough = $balance !== null && $balance >= $unlockCost;
        // Expliciet het dashboard-panel: deze publieke pagina valt anders terug op het admin-panel.
        $creditsUrl = \App\Filament\Dashboard\Pages\Credits::getUrl(panel: 'dashboard');
    @endphp

    <div>
        <p class="mb-4 inline-flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-ink/55">
            <span class="inline-block h-px w-9 bg-accent-500"></span> Verhuurder
        </p>
        <h2 class="mb-6 text-xl font-medium tracking-[-0.02em]">Contactgegevens</h2>

        {{-- Flash --}}
        @if (session('unlock_status'))
            <div class="mb-4 flex items-start gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('unlock_status') }}</span>
            </div>
        @endif
        @if (session('unlock_error'))
            <div class="mb-4 flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                <span>{{ session('unlock_error') }}</span>
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-hairline">
            {{-- Kop: naam + avatar (altijd zichtbaar) --}}
            <div class="flex items-center gap-4 px-4 py-4 sm:px-6 sm:py-5">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#00101e] text-sm font-medium tracking-[0.02em] text-white">
                    {{ $initials ?: '—' }}
                </span>
                <div class="min-w-0">
                    <p class="truncate text-base font-medium leading-tight tracking-[-0.01em] text-ink">{{ $landlord->full_name }}</p>
                    <p class="mt-0.5 truncate text-sm text-ink/55">Verhuurder van dit kot</p>
                </div>
            </div>

            @if ($canView)
                {{-- Ontgrendeld: echte gegevens --}}
                <dl class="divide-y divide-hairline border-t border-hairline">
                    <div class="flex items-center gap-3 px-4 py-3.5 sm:px-6 sm:py-4">
                        <dt class="shrink-0"><x-filament::icon icon="heroicon-o-envelope" class="h-4 w-4 text-ink/40" /></dt>
                        <dd class="min-w-0"><a href="mailto:{{ $landlord->email }}" class="truncate text-sm text-ink transition-colors hover:text-accent-600">{{ $landlord->email }}</a></dd>
                    </div>
                    @if ($landlord->phone)
                        <div class="flex items-center gap-3 px-4 py-3.5 sm:px-6 sm:py-4">
                            <dt class="shrink-0"><x-filament::icon icon="heroicon-o-phone" class="h-4 w-4 text-ink/40" /></dt>
                            <dd><a href="tel:{{ $landlord->phone }}" class="text-sm text-ink transition-colors hover:text-accent-600">{{ $landlord->phone }}</a></dd>
                        </div>
                    @endif
                </dl>
            @else
                {{-- Op slot: gemaskeerde teaser + actie --}}
                <div class="relative border-t border-hairline">
                    <dl class="divide-y divide-hairline" aria-hidden="true">
                        <div class="flex items-center gap-3 px-4 py-3.5 sm:px-6 sm:py-4">
                            <x-filament::icon icon="heroicon-o-envelope" class="h-4 w-4 shrink-0 text-ink/30" />
                            <span class="select-none truncate text-sm text-ink/70 blur-[5px]">naam.verhuurder@voorbeeld.be</span>
                        </div>
                        <div class="flex items-center gap-3 px-4 py-3.5 sm:px-6 sm:py-4">
                            <x-filament::icon icon="heroicon-o-phone" class="h-4 w-4 shrink-0 text-ink/30" />
                            <span class="select-none text-sm text-ink/70 blur-[5px]">+32 4xx xx xx xx</span>
                        </div>
                    </dl>

                    <div class="flex flex-col gap-3 border-t border-hairline bg-canvas-deep px-4 py-4 sm:px-6 sm:py-5">
                        <div class="flex items-start gap-2.5 text-sm text-ink/70">
                            <x-filament::icon icon="heroicon-o-lock-closed" class="mt-0.5 h-4 w-4 shrink-0 text-ink/40" />
                            <span>Ontgrendel de contactgegevens van deze verhuurder. Eén ontgrendeling geldt meteen voor <strong class="font-medium text-ink">al hun panden</strong>.</span>
                        </div>

                        @guest
                            <a href="{{ route('filament.dashboard.auth.login') }}"
                               class="inline-flex h-11 items-center justify-center rounded-[4px] bg-[#00101e] px-5 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors duration-300 hover:bg-[#001f3d]">
                                Log in om te ontgrendelen
                            </a>
                        @else
                            @if ($isHuurder)
                                @if ($hasEnough)
                                    <form method="POST" action="{{ route('rooms.unlock-landlord', $room) }}">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex h-11 w-full items-center justify-center rounded-[4px] bg-[#00101e] px-5 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors duration-300 hover:bg-[#001f3d] sm:w-auto">
                                            Ontgrendelen · {{ $unlockCost }} {{ \Illuminate\Support\Str::plural('credit', $unlockCost) }}
                                        </button>
                                    </form>
                                    <p class="text-xs text-ink/45">Je saldo: {{ $balance }} {{ \Illuminate\Support\Str::plural('credit', $balance) }}</p>
                                @else
                                    <div class="flex flex-col gap-2">
                                        <a href="{{ $creditsUrl }}"
                                           class="inline-flex h-11 items-center justify-center rounded-[4px] bg-accent-500 px-5 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors duration-300 hover:bg-accent-600 sm:w-auto">
                                            Koop credits
                                        </a>
                                        <p class="text-xs text-ink/45">
                                            Je hebt {{ $balance }} {{ \Illuminate\Support\Str::plural('credit', $balance) }}, je hebt er {{ $unlockCost }} nodig.
                                        </p>
                                    </div>
                                @endif
                            @else
                                <p class="text-xs text-ink/45">Ontgrendelen kan met een huurder-account.</p>
                            @endif
                        @endguest
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif
