
@php
    $room = $invitation->room;
    $state = $invitation->completed_at !== null ? 'completed' : ($invitation->expires_at->isPast() ? 'expired' : 'open');

    $criteria = [
        'score_hygiene' => ['label' => 'Hygiëne', 'hint' => 'Staat van de kamer, het sanitair en de gedeelde ruimtes.'],
        'score_size' => ['label' => 'Grootte', 'hint' => 'Was de ruimte wat je ervan verwachtte?'],
        'score_value' => ['label' => 'Prijs-kwaliteit', 'hint' => 'Kreeg je waar voor je huurprijs?'],
        'score_communication' => ['label' => 'Communicatie verhuurder', 'hint' => 'Bereikbaarheid, duidelijke afspraken en opvolging.'],
    ];
@endphp
<x-layout title="Beoordeel je kot · KotKompas" body-class="bg-primary-900 text-white overflow-x-hidden">
<x-slot:head>
    {{-- Token-pagina: mag nooit in een zoekindex belanden. --}}
    <meta name="robots" content="noindex, nofollow">
</x-slot:head>
<div class="kkc-auth relative min-h-dvh">

    {{-- Background: same Antwerp hero + navy scrim as the login/register auth pages --}}
    <div class="fixed inset-0 z-0" aria-hidden="true">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('img/hero-test.jpg') }}');"></div>
        <div class="absolute inset-0 bg-linear-to-tr from-primary-900 via-primary-900/75 to-primary-900/20"></div>
    </div>

    {{-- Fixed editorial header — white wordmark, left (mirrors auth) --}}
    <header class="fixed inset-x-0 top-0 z-10 flex items-start justify-between gap-4 px-[clamp(1.5rem,4vw,3.5rem)] py-[clamp(1rem,2.25vw,1rem)]">
        <a href="/" class="inline-flex">
            <img src="{{ asset('/img/400pxX100pxWoordLogoLiggendZwart.png') }}" alt="KotKompas" class="h-18 w-auto [filter:brightness(0)_invert(1)]">
        </a>
    </header>

    {{-- Stage: frosted form panel left-of-centre, kot-info aside top-right --}}
    <div class="relative z-[1] flex min-h-dvh items-start justify-between gap-8 px-[clamp(1.5rem,4vw,3.5rem)] pt-[clamp(6rem,14vh,9rem)] pb-[clamp(3rem,8vh,5rem)]">

        <main class="kkc-panel w-[min(34rem,92vw)] rounded-[10px] border border-white/22 bg-white/12 p-[clamp(1.5rem,2.4vw,2.25rem)] shadow-[0_24px_70px_rgba(0,0,0,0.32)] backdrop-blur-[14px]">

            @if ($state === 'completed')

                <header>
                    <h1 class="text-[clamp(2.75rem,5vw,4.25rem)] font-medium leading-[0.85] tracking-[-0.05em] text-white">
                        Bedankt!
                    </h1>
                    <p class="mt-4 max-w-prose text-sm leading-relaxed text-white/75">
                        Je beoordeling is verwerkt en telt vanaf nu mee in de kotscore van dit kot.
                        Zo help je de volgende student aan een eerlijker beeld.
                    </p>
                </header>
                <div class="mt-7">
                    <a href="{{ route('rooms.index') }}" class="kkc-cta group">
                        <span class="kkc-cta-label">Bekijk koten</span>
                        <span class="kkc-cta-chip" aria-hidden="true">
                            <svg class="kkc-cta-arrow kkc-cta-arrow--out" viewBox="0 0 16 16" fill="none"><path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <svg class="kkc-cta-arrow kkc-cta-arrow--in" viewBox="0 0 16 16" fill="none"><path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                    </a>
                </div>

            @elseif ($state === 'expired')

                <header>
                    <h1 class="text-[clamp(2.75rem,5vw,4.25rem)] font-medium leading-[0.85] tracking-[-0.05em] text-white">
                        Link verlopen
                    </h1>
                    <p class="mt-4 max-w-prose text-sm leading-relaxed text-white/75">
                        Deze beoordelingslink is niet meer geldig. Vraag je verhuurder om een
                        nieuwe link, of <a href="{{ route('contact') }}" class="font-medium text-white underline underline-offset-2 hover:text-secondary-300">contacteer ons</a> — dan sturen we je er een.
                    </p>
                </header>

            @else

                <header class="mb-7">
                    <h1 class="text-[clamp(2.75rem,5vw,4.25rem)] font-medium leading-[0.85] tracking-[-0.05em] text-white">
                        Hoe was je kot?
                    </h1>
                    <p class="mt-4 max-w-prose text-sm leading-relaxed text-white/75">
                        Je huurde {{ $room->title ?: 'een kot' }} — beoordeel het in vier vragen.
                        Je naam wordt nergens getoond.
                    </p>
                    <p class="mt-2 text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-white/55">
                        1 = ondermaats &nbsp;·&nbsp; 5 = uitstekend
                    </p>
                </header>

                <form method="POST" action="{{ route('reviews.store', $invitation) }}" data-review-form class="flex flex-col gap-6">
                    @csrf

                    {{-- Honeypot: bots fill it, humans never see it. Backend rejects if non-empty. --}}
                    <div class="absolute -left-[9999px]" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                    </div>

                    @foreach ($criteria as $field => $criterion)
                        <fieldset>
                            <legend class="block text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-white/82">
                                {{ $criterion['label'] }} <span class="text-accent-500">*</span>
                            </legend>
                            <p class="mt-1 mb-3 text-sm text-white/60">{{ $criterion['hint'] }}</p>
                            <div class="flex gap-2">
                                @for ($i = 1; $i <= 5; $i++)
                                    <label class="cursor-pointer">
                                        <input
                                            type="radio" name="{{ $field }}" value="{{ $i }}" required
                                            class="peer sr-only" @checked((int) old($field) === $i)
                                        >
                                        <span class="flex h-10 w-10 items-center justify-center rounded-[3px] border border-white/35 text-sm text-white/85 transition-colors duration-150 hover:border-white peer-checked:border-white peer-checked:bg-white peer-checked:font-semibold peer-checked:text-primary-900 peer-focus-visible:ring-2 peer-focus-visible:ring-white/40">
                                            {{ $i }}
                                        </span>
                                    </label>
                                @endfor
                            </div>
                            @error($field) <p class="mt-1.5 text-sm text-accent-300">{{ $message }}</p> @enderror
                        </fieldset>
                    @endforeach

                    {{-- Submit: white pill + arrow chip (the auth "kk-cta") --}}
                    <div class="mt-1">
                        <button type="submit" data-submit class="kkc-cta group">
                            <span class="kkc-cta-label" data-label>Verstuur beoordeling</span>
                            <span class="kkc-cta-chip" aria-hidden="true">
                                <svg class="kkc-cta-arrow kkc-cta-arrow--out" viewBox="0 0 16 16" fill="none"><path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <svg class="kkc-cta-arrow kkc-cta-arrow--in" viewBox="0 0 16 16" fill="none"><path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <svg data-spinner class="ml-2 hidden h-5 w-5 animate-spin text-primary-900" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                        </button>
                    </div>
                </form>

            @endif
        </main>

        {{-- Aside: kot-info, mirrors the auth value-prop rail. Het adres staat
             hier bewust: de link is bedoeld voor de ex-huurder, die moet
             herkennen wélk kot hij beoordeelt. --}}
        <aside lang="nl" class="hidden shrink-0 max-w-xs self-start pt-[0.4rem] text-right text-white [text-shadow:0_1px_18px_rgba(0,16,30,0.9)] md:block">
            <p class="flex items-center justify-end gap-2 text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-white/70">
                Jouw kot
                <span class="inline-block h-px w-6 bg-accent" aria-hidden="true"></span>
            </p>
            <dl class="mt-5 divide-y divide-white/20">
                @if ($room->title)
                    <div class="py-3">
                        <dt class="text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-white/55">Kot</dt>
                        <dd class="mt-1 text-[0.9rem] leading-snug tracking-[-0.01em]">{{ $room->title }}</dd>
                    </div>
                @endif
                <div class="py-3">
                    <dt class="text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-white/55">Adres</dt>
                    <dd class="mt-1 text-[0.9rem] leading-snug tracking-[-0.01em]">{{ $room->full_address }}</dd>
                </div>
                <div class="py-3">
                    <dt class="text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-white/55">Anoniem</dt>
                    <dd class="mt-1 text-[0.9rem] leading-snug tracking-[-0.01em]">Je beoordeling wordt nooit aan je naam gekoppeld — ook niet voor de verhuurder.</dd>
                </div>
                @if ($state === 'open')
                    <div class="py-3">
                        <dt class="text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-white/55">Geldig tot</dt>
                        <dd class="mt-1 text-[0.9rem] leading-snug tracking-[-0.01em]">{{ $invitation->expires_at->format('d-m-Y') }}</dd>
                    </div>
                @endif
            </dl>
        </aside>
    </div>
</div>

{{-- Auth-matched control styling (CTA pill arrow), ported from the Filament
     theme.css which only loads inside the panel, not on public pages. --}}
<style>
    /* white pill CTA with sliding arrow chip — the auth "Start a project" button */
    .kkc-cta {
        display: inline-flex; align-items: center; gap: 0.5rem;
        height: 2.6rem; padding: 0 0.3rem 0 1.1rem;
        border: 0; border-radius: 3px; background-color: #fff;
        color: var(--color-primary-900, #00101e);
        font-family: 'area-normal', sans-serif; font-weight: 500; font-size: 0.8125rem;
        cursor: pointer; transition: opacity 160ms ease;
        text-decoration: none;
    }
    .kkc-cta:disabled { cursor: not-allowed; opacity: 0.6; }
    .kkc-cta-chip {
        position: relative; display: inline-flex; height: 1.85rem; width: 1.85rem;
        flex-shrink: 0; overflow: hidden; border-radius: 2px;
        background-color: var(--color-primary-700, #002f5b); color: #fff;
    }
    .kkc-cta-arrow {
        position: absolute; inset: 0; margin: auto; width: 0.8rem; height: auto;
        transition: transform 0.5s cubic-bezier(0.65,0,0.35,1);
    }
    .kkc-cta-arrow--in { transform: translate(-220%, 220%); }
    .kkc-cta:hover .kkc-cta-arrow--out { transform: translate(220%, -220%); }
    .kkc-cta:hover .kkc-cta-arrow--in { transform: translate(0, 0); }

    /* staggered build-in, matching the auth entrance */
    @media (prefers-reduced-motion: no-preference) {
        @keyframes kkc-rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
        .kkc-auth header a { animation: kkc-rise 560ms cubic-bezier(0.16,1,0.3,1) both; }
        .kkc-panel { animation: kkc-rise 560ms cubic-bezier(0.16,1,0.3,1) 160ms both; }
        .kkc-auth aside { animation: kkc-rise 560ms cubic-bezier(0.16,1,0.3,1) 260ms both; }
    }
</style>

<script>
    document.querySelector('[data-review-form]')?.addEventListener('submit', (e) => {
        const btn = e.currentTarget.querySelector('[data-submit]');
        if (!btn) return;
        btn.disabled = true;
        btn.querySelector('[data-label]').textContent = 'Versturen…';
        btn.querySelector('[data-spinner]').classList.remove('hidden');
    });
</script>
</x-layout>
