<div>
    @if ($landlord)
        <p class="mb-4 inline-flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-ink/55">
            <span class="inline-block h-px w-9 bg-accent-500"></span> Verhuurder
        </p>
        <h2 class="mb-6 text-xl font-medium tracking-[-0.02em]">Contact</h2>

        {{-- Vluchtige melding (verdwijnt vanzelf, geen page refresh) --}}
        @if ($flashMessage)
            <div wire:key="flash-{{ $flashTick }}"
                 x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show"
                 x-transition:leave="transition ease-in duration-300" x-transition:leave-end="opacity-0"
                 @class([
                    'mb-4 flex items-start gap-2.5 rounded-xl px-4 py-3 text-sm',
                    'border border-emerald-200 bg-emerald-50 text-emerald-800' => $flashType === 'success',
                    'border border-amber-200 bg-amber-50 text-amber-800' => $flashType !== 'success',
                 ])>
                @if ($flashType === 'success')
                    <x-heroicon-o-check-circle class="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true" />
                @else
                    <x-heroicon-o-exclamation-triangle class="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true" />
                @endif
                <span>{{ $flashMessage }}</span>
            </div>
        @endif

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
                    @if (! $showForm)
                        <button type="button" wire:click="$set('showForm', true)"
                                class="w-full rounded-[4px] bg-primary-900 py-3 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors hover:bg-ink sm:w-auto sm:px-6">
                            Stuur een bericht
                        </button>
                    @else
                        <form wire:submit="sendMessage" class="space-y-3">
                            <textarea wire:model="body" rows="4" maxlength="5000"
                                      placeholder="Stel je vraag of toon je interesse…"
                                      class="w-full rounded-xl border border-hairline bg-canvas px-3 py-2.5 text-sm text-ink placeholder:text-ink/40 focus:border-ink/30 focus:outline-none focus:ring-2 focus:ring-primary-500/30"></textarea>
                            @error('body')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            <div class="flex items-center gap-2">
                                <button type="submit" wire:loading.attr="disabled" wire:target="sendMessage"
                                        class="flex-1 rounded-xl bg-primary-900 py-2.5 text-sm font-medium text-white transition-colors hover:bg-ink disabled:opacity-50">
                                    <span wire:loading.remove wire:target="sendMessage">Versturen</span>
                                    <span wire:loading wire:target="sendMessage">Bezig…</span>
                                </button>
                                <button type="button" wire:click="$set('showForm', false)"
                                        class="rounded-xl border border-hairline px-4 py-2.5 text-sm text-ink/70 transition-colors hover:bg-ink/[0.04]">
                                    Annuleer
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

        @else
            {{-- Op slot. GEEN echte verhuurderdata in de DOM: alles hieronder is dummy,
                 zodat de blur niet via JS te omzeilen is. Grid-stack zodat de overlay
                 de hoogte bepaalt en alles zichtbaar blijft. --}}
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

                {{-- Overlay (scherp + klikbaar) in normale flow → bepaalt de hoogte zodat alles zichtbaar is --}}
                <div class="relative z-10 flex flex-col items-center justify-center gap-3 bg-canvas/70 px-6 py-10 text-center backdrop-blur-[3px]">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-900/10">
                        <x-heroicon-o-lock-closed class="h-5 w-5 text-primary-900" aria-hidden="true" />
                    </div>
                    <p class="text-sm font-medium text-ink">Verhuurdergegevens vergrendeld</p>
                    <p class="max-w-[20rem] text-xs leading-relaxed text-ink/55">
                        Ontgrendel de contactgegevens en het berichtkanaal. Eén ontgrendeling geldt meteen voor <strong class="font-medium text-ink/80">al hun panden</strong>.
                    </p>

                    @guest
                        <a href="{{ url('/dashboard/login') }}"
                           class="mt-1 inline-flex h-11 items-center justify-center rounded-[4px] bg-primary-900 px-5 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors duration-300 hover:bg-ink">
                            Log in om te ontgrendelen
                        </a>
                    @else
                        @if ($isHuurder)
                            @if ($balance >= $cost)
                                <button type="button" wire:click="unlock" wire:loading.attr="disabled" wire:target="unlock"
                                        class="mt-1 inline-flex h-11 items-center justify-center rounded-[4px] bg-primary-900 px-5 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors duration-300 hover:bg-ink disabled:opacity-50">
                                    <span wire:loading.remove wire:target="unlock">Ontgrendelen · {{ $cost }} {{ \Illuminate\Support\Str::plural('credit', $cost) }}</span>
                                    <span wire:loading wire:target="unlock">Bezig…</span>
                                </button>
                                <p class="text-xs text-ink/45">Je saldo: {{ $balance }} {{ \Illuminate\Support\Str::plural('credit', $balance) }}</p>
                            @else
                                <a href="{{ \App\Filament\Dashboard\Pages\Credits::getUrl(panel: 'dashboard') }}"
                                   class="mt-1 inline-flex h-11 items-center justify-center rounded-[4px] bg-accent-500 px-5 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors duration-300 hover:bg-accent-600">
                                    Koop credits
                                </a>
                                <p class="text-xs text-ink/45">Je hebt {{ $balance }} {{ \Illuminate\Support\Str::plural('credit', $balance) }}, je hebt er {{ $cost }} nodig.</p>
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
