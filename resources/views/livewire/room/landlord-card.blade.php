<div>
    @if ($landlord)
        <p class="mb-4 inline-flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-ink/55">
            <span class="inline-block h-px w-9 bg-accent-500"></span> Verhuurder
        </p>
        <h2 class="mb-6 text-xl font-medium tracking-[-0.02em]">Contact</h2>

        @if ($isOwn)
            {{-- Eigen kot --}}
            <div class="rounded-2xl border border-hairline bg-canvas-deep px-4 py-4 text-sm text-ink/65 sm:px-6 sm:py-5">
                Dit is jouw kot. Geïnteresseerde huurders kunnen je hier rechtstreeks een bericht sturen.
            </div>

        @elseif ($canView)
            {{-- Ontgrendeld: echte gegevens + berichtkanaal --}}
            @php $memberSince = $landlord->created_at?->format('Y'); @endphp
            <div class="overflow-hidden rounded-2xl border border-hairline">
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
                        <dd class="min-w-0"><a href="mailto:{{ $landlord->email }}" class="truncate text-sm text-ink underline-offset-2 transition-colors hover:text-accent-600 hover:underline">{{ $landlord->email }}</a></dd>
                    </div>
                    @if ($landlord->phone)
                        <div class="flex items-center gap-3 px-4 py-3.5 sm:px-6 sm:py-4">
                            <dt class="shrink-0"><x-heroicon-o-phone class="h-4 w-4 text-ink/40" /></dt>
                            <dd><a href="tel:{{ $landlord->phone }}" class="text-sm text-ink underline-offset-2 transition-colors hover:text-accent-600 hover:underline">{{ $landlord->phone }}</a></dd>
                        </div>
                    @endif
                </dl>

                {{-- In-app berichtkanaal --}}
                <div class="border-t border-hairline bg-canvas-deep px-4 py-4 sm:px-6 sm:py-5">
                    @if ($sent)
                        <p class="flex items-center gap-2 text-sm text-ink/70">
                            <x-heroicon-o-check-circle class="h-4 w-4 shrink-0 text-accent-600" aria-hidden="true" />
                            Je bericht is verstuurd. Je vindt het gesprek terug bij Berichten in je dashboard.
                        </p>
                    @else
                        {{-- <details>: opent direct (native, geen server-round-trip) --}}
                        <details class="group/msg" @error('body') open @enderror>
                            <summary class="inline-flex h-11 cursor-pointer list-none items-center justify-center rounded-[4px] bg-primary-900 px-6 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors duration-200 hover:bg-ink [&::-webkit-details-marker]:hidden">
                                <span class="group-open/msg:hidden">Stuur een bericht</span>
                                <span class="hidden group-open/msg:inline">Bericht annuleren</span>
                            </summary>

                            <form wire:submit="sendMessage" class="mt-4 space-y-3">
                                <textarea wire:model="body" rows="4" maxlength="5000"
                                          placeholder="Stel je vraag of toon je interesse…"
                                          class="w-full rounded-xl border border-hairline bg-canvas px-3 py-2.5 text-sm text-ink placeholder:text-ink/40 transition-colors focus:border-ink/30 focus:outline-none focus:ring-2 focus:ring-primary-500/30"></textarea>
                                @error('body')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <button type="submit" wire:loading.attr="disabled" wire:target="sendMessage"
                                        class="inline-flex h-11 items-center justify-center rounded-xl bg-primary-900 px-6 text-sm font-medium text-white transition-colors duration-200 hover:bg-ink disabled:opacity-50">
                                    <span wire:loading.remove wire:target="sendMessage">Versturen</span>
                                    <span wire:loading wire:target="sendMessage">Bezig…</span>
                                </button>
                            </form>
                        </details>
                    @endif
                </div>
            </div>

        @else
            {{-- Op slot. GEEN echte verhuurderdata in de DOM: alles hieronder is dummy.
                 ($isHuurder, $balance en $cost komen uit de component-render.) --}}
            @php
                $creditsUrl = \App\Filament\Dashboard\Pages\Credits::getUrl(panel: 'dashboard');
            @endphp

            <div class="relative overflow-hidden rounded-2xl border border-hairline">
                {{-- Dummy placeholder (geblurd), absoluut achter de overlay --}}
                <div class="pointer-events-none absolute inset-0 select-none blur-[7px]" aria-hidden="true">
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

                {{-- Overlay (scherp + klikbaar) in normale flow → bepaalt de hoogte --}}
                <div class="relative z-10 flex flex-col items-center justify-center gap-3 bg-canvas/70 px-6 py-10 text-center backdrop-blur-[3px]">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-900/10">
                        <x-heroicon-o-lock-closed class="h-5 w-5 text-primary-900" aria-hidden="true" />
                    </div>
                    <p class="text-sm font-medium text-ink">Verhuurdergegevens vergrendeld</p>
                    <p class="max-w-[20rem] text-xs leading-relaxed text-ink/55">
                        Ontgrendel de contactgegevens en het berichtkanaal. Eén ontgrendeling geldt meteen voor <strong class="font-medium text-ink/80">al hun panden</strong>.
                    </p>

                    @guest
                        <button type="button" wire:click="loginToUnlock"
                                class="mt-1 inline-flex h-11 items-center justify-center rounded-[4px] bg-primary-900 px-5 text-xs font-medium uppercase tracking-[0.04em] text-white shadow-sm transition-all duration-200 hover:bg-ink hover:shadow-md">
                            Log in om te ontgrendelen
                        </button>
                    @else
                        @if ($isHuurder)
                            @if ($balance >= $cost)
                                <button type="button" wire:click="unlock" wire:loading.attr="disabled" wire:target="unlock"
                                        class="mt-1 inline-flex h-11 items-center justify-center rounded-[4px] bg-primary-900 px-5 text-xs font-medium uppercase tracking-[0.04em] text-white shadow-sm transition-all duration-200 hover:bg-ink hover:shadow-md disabled:opacity-50">
                                    <span wire:loading.remove wire:target="unlock">Ontgrendelen · {{ $cost }} {{ \Illuminate\Support\Str::plural('credit', $cost) }}</span>
                                    <span wire:loading wire:target="unlock">Bezig…</span>
                                </button>
                                <p class="text-xs text-ink/45">Je saldo: {{ $balance }} {{ \Illuminate\Support\Str::plural('credit', $balance) }}</p>
                            @else
                                <a href="{{ $creditsUrl }}"
                                   class="mt-1 inline-flex h-11 items-center justify-center rounded-[4px] bg-accent-500 px-5 text-xs font-medium uppercase tracking-[0.04em] text-white shadow-sm transition-all duration-200 hover:bg-accent-600 hover:shadow-md">
                                    Koop credits
                                </a>
                                <p class="text-xs text-ink/45">Je hebt {{ $balance }} {{ \Illuminate\Support\Str::plural('credit', $balance) }}, je hebt er {{ $cost }} nodig.</p>
                            @endif

                            @if ($unlockError)
                                <p class="text-xs text-red-600">{{ $unlockError }}</p>
                            @endif
                        @else
                            <p class="text-xs text-ink/45">Ontgrendelen kan met een huurder-account.</p>
                        @endif
                    @endguest
                </div>
            </div>
        @endif
    @endif
</div>
