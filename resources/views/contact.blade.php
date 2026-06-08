<x-layout title="Contact · KotKompas" body-class="bg-primary-900 text-white overflow-x-hidden">
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

    {{-- Stage: frosted form panel left-of-centre, value aside top-right --}}
    <div class="relative z-[1] flex min-h-dvh items-start justify-between gap-8 px-[clamp(1.5rem,4vw,3.5rem)] pt-[clamp(6rem,14vh,9rem)] pb-[clamp(3rem,8vh,5rem)]">

        <main class="kkc-panel w-[min(34rem,92vw)] rounded-[10px] border border-white/22 bg-white/12 p-[clamp(1.5rem,2.4vw,2.25rem)] shadow-[0_24px_70px_rgba(0,0,0,0.32)] backdrop-blur-[14px]">

            <header class="mb-7">
                <h1 class="text-[clamp(2.75rem,5vw,4.25rem)] font-medium leading-[0.85] tracking-[-0.05em] text-white">
                    Contact
                </h1>
                <p class="mt-4 max-w-prose text-sm leading-relaxed text-white/75">
                    Vraag, feedback of samenwerking? Vul het formulier in — we antwoorden doorgaans binnen 24&nbsp;uur.
                </p>
            </header>

            {{-- Success flash (backend sets session('success')) --}}
            @if (session('success'))
                <div class="mb-6 rounded-md border border-white/25 bg-white/15 px-4 py-3 text-sm text-white" role="status">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Error flash (backend sets session('error') on send failure) --}}
            @if (session('error'))
                <div class="mb-6 rounded-md border border-accent-400/60 bg-accent-500/20 px-4 py-3 text-sm text-white" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('contact.send') }}" data-contact-form class="flex flex-col gap-5">
                @csrf

                {{-- Honeypot: bots fill it, humans never see it. Backend rejects if non-empty. --}}
                <div class="absolute -left-[9999px]" aria-hidden="true">
                    <label for="website">Website</label>
                    <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                </div>

                {{-- Naam --}}
                <div>
                    <label for="name" class="mb-2 block text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-white/82">
                        Naam <span class="text-accent-500">*</span>
                    </label>
                    <input
                        type="text" name="name" id="name" value="{{ old('name') }}" required autocomplete="name"
                        @error('name') aria-invalid="true" @enderror
                        class="block w-full rounded-none border-0 border-b border-white/45 bg-transparent px-0 py-1.5 text-lg text-white transition-[border-color] placeholder:text-white/40 focus:border-white focus:outline-none focus:ring-0"
                    >
                    @error('name') <p class="mt-1.5 text-sm text-accent-300">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="mb-2 block text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-white/82">
                        E-mail <span class="text-accent-500">*</span>
                    </label>
                    <input
                        type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="email"
                        @error('email') aria-invalid="true" @enderror
                        class="block w-full rounded-none border-0 border-b border-white/45 bg-transparent px-0 py-1.5 text-lg text-white transition-[border-color] placeholder:text-white/40 focus:border-white focus:outline-none focus:ring-0"
                    >
                    @error('email') <p class="mt-1.5 text-sm text-accent-300">{{ $message }}</p> @enderror
                </div>

                {{-- Onderwerp --}}
                <div>
                    <label for="subject" class="mb-2 block text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-white/82">
                        Onderwerp <span class="text-accent-500">*</span>
                    </label>
                    <select
                        name="subject" id="subject" required
                        @error('subject') aria-invalid="true" @enderror
                        class="block w-full rounded-none border-0 border-b border-white/45 bg-transparent px-0 py-1.5 text-lg text-white transition-[border-color] focus:border-white focus:outline-none focus:ring-0 [&>option]:bg-primary-900 [&>option]:text-white"
                    >
                        <option value="" disabled {{ old('subject') ? '' : 'selected' }}>Kies een onderwerp…</option>
                        @foreach (['Algemene vraag', 'Feedback', 'Samenwerking', 'Probleem melden', 'Anders'] as $option)
                            <option value="{{ $option }}" @selected(old('subject') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                    @error('subject') <p class="mt-1.5 text-sm text-accent-300">{{ $message }}</p> @enderror
                </div>

                {{-- Bericht --}}
                <div>
                    <label for="message" class="mb-2 block text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-white/82">
                        Bericht <span class="text-accent-500">*</span>
                    </label>
                    <textarea
                        name="message" id="message" rows="5" required minlength="10"
                        @error('message') aria-invalid="true" @enderror
                        class="block w-full resize-y rounded-none border-0 border-b border-white/45 bg-transparent px-0 py-1.5 text-lg text-white transition-[border-color] placeholder:text-white/40 focus:border-white focus:outline-none focus:ring-0"
                    >{{ old('message') }}</textarea>
                    @error('message') <p class="mt-1.5 text-sm text-accent-300">{{ $message }}</p> @enderror
                </div>

                {{-- GDPR consent --}}
                <div>
                    <label class="flex items-start gap-3 text-sm leading-relaxed text-white/75">
                        <input
                            type="checkbox" name="consent" value="1" required @checked(old('consent'))
                            @error('consent') aria-invalid="true" @enderror
                            class="kkc-check mt-0.5 h-4 w-4 shrink-0 cursor-pointer appearance-none rounded-[3px] border-[1.5px] border-white/45 bg-transparent transition-[border-color] checked:border-white focus:outline-none focus:ring-2 focus:ring-white/30"
                        >
                        <span>
                            Ik ga akkoord dat mijn gegevens verwerkt worden om mijn bericht te beantwoorden.
                            <a href="/privacy" class="font-medium text-white underline underline-offset-2 hover:text-secondary-300">Privacybeleid</a>.
                        </span>
                    </label>
                    @error('consent') <p class="mt-1.5 text-sm text-accent-300">{{ $message }}</p> @enderror
                </div>

                {{-- Submit: white pill + arrow chip (the auth "kk-cta") --}}
                <div class="mt-3">
                    <button type="submit" data-submit class="kkc-cta group">
                        <span class="kkc-cta-label" data-label>Verstuur bericht</span>
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
        </main>

        {{-- Aside: contact channels, mirrors the auth value-prop rail --}}
        <aside lang="nl" class="hidden shrink-0 max-w-xs self-start pt-[0.4rem] text-right text-white [text-shadow:0_1px_18px_rgba(0,16,30,0.9)] md:block">
            <p class="flex items-center justify-end gap-2 text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-white/70">
                Bereik ons
                <span class="inline-block h-px w-6 bg-accent" aria-hidden="true"></span>
            </p>
            <dl class="mt-5 divide-y divide-white/20">
                <div class="py-3">
                    <dt class="text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-white/55">E-mail</dt>
                    <dd class="mt-1 text-[0.9rem] leading-snug tracking-[-0.01em]">
                        <a href="mailto:hallo@kotkompas.be" class="hover:text-secondary-300">hallo@kotkompas.be</a>
                    </dd>
                </div>
                <div class="py-3">
                    <dt class="text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-white/55">Reactietijd</dt>
                    <dd class="mt-1 text-[0.9rem] leading-snug tracking-[-0.01em]">Doorgaans binnen 24 uur op werkdagen.</dd>
                </div>
                <div class="py-3">
                    <dt class="text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-white/55">Privacy</dt>
                    <dd class="mt-1 text-[0.9rem] leading-snug tracking-[-0.01em]">Je gegevens worden enkel gebruikt om je vraag te beantwoorden.</dd>
                </div>
            </dl>
        </aside>
    </div>
</div>

{{-- Auth-matched control styling (checkbox mark + CTA pill arrow), ported from the
     Filament theme.css which only loads inside the panel, not on public pages. --}}
<style>
    /* consent checkbox: white dash → circle, matching the auth controls */
    .kkc-check { position: relative; }
    .kkc-check::after {
        content: ""; position: absolute; left: 50%; top: 50%;
        width: 2px; height: 58%; transform: translate(-50%, -50%);
        border-radius: 1px; background-color: #fff;
        transition: width 280ms cubic-bezier(0.22,1,0.36,1), height 280ms cubic-bezier(0.22,1,0.36,1), border-radius 280ms cubic-bezier(0.22,1,0.36,1);
    }
    .kkc-check:checked::after { width: 0.55rem; height: 0.55rem; border-radius: 50%; }

    /* white pill CTA with sliding arrow chip — the auth "Start a project" button */
    .kkc-cta {
        display: inline-flex; align-items: center; gap: 0.5rem;
        height: 2.6rem; padding: 0 0.3rem 0 1.1rem;
        border: 0; border-radius: 3px; background-color: #fff;
        color: var(--color-primary-900, #00101e);
        font-family: 'area-normal', sans-serif; font-weight: 500; font-size: 0.8125rem;
        cursor: pointer; transition: opacity 160ms ease;
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
    document.querySelector('[data-contact-form]')?.addEventListener('submit', (e) => {
        const btn = e.currentTarget.querySelector('[data-submit]');
        if (!btn) return;
        btn.disabled = true;
        btn.querySelector('[data-label]').textContent = 'Versturen…';
        btn.querySelector('[data-spinner]').classList.remove('hidden');
    });
</script>
</x-layout>
