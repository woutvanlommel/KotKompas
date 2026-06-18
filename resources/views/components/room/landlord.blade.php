@props(['room', 'canView' => false, 'unlockCost' => 1])

@php
    $landlord = $room->building?->landlord;
    $user = auth()->user();
    $isOwnListing = $user && $landlord && $user->id === $landlord->id;
@endphp

@if ($landlord)
    <div>
        <p class="mb-4 inline-flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-ink/55">
            <span class="inline-block h-px w-9 bg-accent-500"></span> Verhuurder
        </p>
        <h2 class="mb-6 text-xl font-medium tracking-[-0.02em]">Contact</h2>

        {{-- Flash --}}
        @if (session('unlock_status'))
            <div class="mb-4 flex items-start gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <x-heroicon-o-check-circle class="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true" />
                <span>{{ session('unlock_status') }}</span>
            </div>
        @endif
        @if (session('unlock_error'))
            <div class="mb-4 flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                <x-heroicon-o-exclamation-triangle class="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true" />
                <span>{{ session('unlock_error') }}</span>
            </div>
        @endif

        @if ($isOwnListing)
            {{-- Eigen kot: geen ontgrendeling nodig --}}
            <div class="rounded-2xl border border-hairline bg-canvas-deep px-4 py-4 text-sm text-ink/65 sm:px-6 sm:py-5">
                Dit is jouw kot. Geïnteresseerde huurders kunnen je hier rechtstreeks een bericht sturen.
            </div>

        @elseif ($canView)
            {{-- Ontgrendeld (gekocht of via huurrelatie): echte gegevens + berichtkanaal --}}
            @php $memberSince = $landlord->created_at?->format('Y'); @endphp
            <div class="overflow-hidden rounded-2xl border border-hairline"
                 x-data="{ open: {{ $errors->has('body') ? 'true' : 'false' }} }">
                <div class="flex items-center gap-4 px-4 py-4 sm:px-6 sm:py-5">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#00101e] text-sm font-medium tracking-[0.02em] text-white">
                        {{ collect(explode(' ', trim((string) $landlord->full_name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '—' }}
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-base font-medium leading-tight tracking-[-0.01em] text-ink">{{ $landlord->full_name }}</p>
                        @if ($memberSince)
                            <p class="mt-0.5 text-sm text-ink/55">Lid sinds {{ $memberSince }}</p>
                        @endif
                    </div>
                </div>

                <dl class="divide-y divide-hairline border-t border-hairline">
                    <div class="flex items-center gap-3 px-4 py-3.5 sm:px-6 sm:py-4">
                        <dt class="shrink-0"><x-heroicon-o-envelope class="h-4 w-4 text-ink/40" /></dt>
                        <dd class="min-w-0"><a href="mailto:{{ $landlord->email }}" class="truncate text-sm text-ink transition-colors hover:text-accent-600">{{ $landlord->email }}</a></dd>
                    </div>
                    @if ($landlord->phone)
                        <div class="flex items-center gap-3 px-4 py-3.5 sm:px-6 sm:py-4">
                            <dt class="shrink-0"><x-heroicon-o-phone class="h-4 w-4 text-ink/40" /></dt>
                            <dd><a href="tel:{{ $landlord->phone }}" class="text-sm text-ink transition-colors hover:text-accent-600">{{ $landlord->phone }}</a></dd>
                        </div>
                    @endif
                </dl>

                {{-- In-app berichtkanaal --}}
                <div class="border-t border-hairline bg-canvas-deep px-4 py-4 sm:px-6 sm:py-5">
                    @if (session('status'))
                        <div class="flex items-start gap-2 rounded-xl border border-accent-500/30 bg-accent-500/10 px-4 py-3 text-xs leading-relaxed text-ink/80">
                            <x-heroicon-o-check-circle class="mt-px h-4 w-4 shrink-0 text-accent-600" aria-hidden="true" />
                            <span>{{ session('status') }}</span>
                        </div>
                    @else
                        <button type="button" x-show="!open" @click="open = true"
                                class="w-full rounded-[4px] bg-primary-900 py-3 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors hover:bg-ink sm:w-auto sm:px-6">
                            Stuur een bericht
                        </button>

                        <form method="POST" action="{{ route('rooms.contact', $room) }}" x-show="open" x-cloak class="space-y-3">
                            @csrf
                            <textarea name="body" rows="4" required maxlength="5000"
                                      placeholder="Stel je vraag of toon je interesse…"
                                      class="w-full rounded-xl border border-hairline bg-canvas px-3 py-2.5 text-sm text-ink placeholder:text-ink/40 focus:border-ink/30 focus:outline-none focus:ring-2 focus:ring-primary-500/30">{{ old('body') }}</textarea>
                            @error('body')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            <div class="flex items-center gap-2">
                                <button type="submit" class="flex-1 rounded-xl bg-primary-900 py-2.5 text-sm font-medium text-white transition-colors hover:bg-ink">
                                    Versturen
                                </button>
                                <button type="button" @click="open = false"
                                        class="rounded-xl border border-hairline px-4 py-2.5 text-sm text-ink/70 transition-colors hover:bg-ink/[0.04]">
                                    Annuleer
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

        @else
            {{-- Op slot. BELANGRIJK: hier komt GEEN echte verhuurderdata in de DOM —
                 alles hieronder is dummy, zodat de blur niet via JS te omzeilen is. --}}
            @php
                $isHuurder = $user?->hasRole('huurder') ?? false;
                $balance = $isHuurder ? app(\App\Services\CreditService::class)->balance($user) : null;
                $hasEnough = $balance !== null && $balance >= $unlockCost;
                $creditsUrl = \App\Filament\Dashboard\Pages\Credits::getUrl(panel: 'dashboard');
            @endphp

            <div class="relative overflow-hidden rounded-2xl border border-hairline">
                {{-- Dummy placeholder, geblurd, niet selecteerbaar, buiten de toegankelijkheidsboom --}}
                <div class="pointer-events-none select-none blur-[7px]" aria-hidden="true">
                    <div class="flex items-center gap-4 px-4 py-4 sm:px-6 sm:py-5">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#00101e] text-sm font-medium text-white">··</span>
                        <div>
                            <p class="text-base font-medium text-ink">Verhuurder verborgen</p>
                            <p class="mt-0.5 text-sm text-ink/55">Lid sinds ····</p>
                        </div>
                    </div>
                    <dl class="divide-y divide-hairline border-t border-hairline">
                        <div class="flex items-center gap-3 px-4 py-3.5 sm:px-6 sm:py-4">
                            <x-heroicon-o-envelope class="h-4 w-4 shrink-0 text-ink/30" />
                            <span class="text-sm text-ink/70">verborgen@kotkompas.be</span>
                        </div>
                        <div class="flex items-center gap-3 px-4 py-3.5 sm:px-6 sm:py-4">
                            <x-heroicon-o-phone class="h-4 w-4 shrink-0 text-ink/30" />
                            <span class="text-sm text-ink/70">+32 ··· ·· ·· ··</span>
                        </div>
                    </dl>
                </div>

                {{-- Overlay met de actie (scherp + klikbaar) --}}
                <div class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-canvas/70 px-6 text-center backdrop-blur-[3px]">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-900/10">
                        <x-heroicon-o-lock-closed class="h-5 w-5 text-primary-900" aria-hidden="true" />
                    </div>
                    <p class="text-sm font-medium text-ink">Verhuurdergegevens vergrendeld</p>
                    <p class="max-w-[18rem] text-xs leading-relaxed text-ink/55">
                        Ontgrendel de contactgegevens en het berichtkanaal. Eén ontgrendeling geldt meteen voor <strong class="font-medium text-ink/80">al hun panden</strong>.
                    </p>

                    @guest
                        <a href="{{ url('/dashboard/login') }}"
                           class="mt-1 inline-flex h-11 items-center justify-center rounded-[4px] bg-primary-900 px-5 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors duration-300 hover:bg-ink">
                            Log in om te ontgrendelen
                        </a>
                    @else
                        @if ($isHuurder)
                            @if ($hasEnough)
                                <form method="POST" action="{{ route('rooms.unlock-landlord', $room) }}" class="mt-1">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex h-11 items-center justify-center rounded-[4px] bg-primary-900 px-5 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors duration-300 hover:bg-ink">
                                        Ontgrendelen · {{ $unlockCost }} {{ \Illuminate\Support\Str::plural('credit', $unlockCost) }}
                                    </button>
                                </form>
                                <p class="text-xs text-ink/45">Je saldo: {{ $balance }} {{ \Illuminate\Support\Str::plural('credit', $balance) }}</p>
                            @else
                                <a href="{{ $creditsUrl }}"
                                   class="mt-1 inline-flex h-11 items-center justify-center rounded-[4px] bg-accent-500 px-5 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors duration-300 hover:bg-accent-600">
                                    Koop credits
                                </a>
                                <p class="text-xs text-ink/45">Je hebt {{ $balance }} {{ \Illuminate\Support\Str::plural('credit', $balance) }}, je hebt er {{ $unlockCost }} nodig.</p>
                            @endif
                        @else
                            <p class="text-xs text-ink/45">Ontgrendelen kan met een huurder-account.</p>
                        @endif
                    @endguest
                </div>
            </div>
        @endif
    </div>
@endif
