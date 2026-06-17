@php
    $revFmt = number_format($revenue, 0, ',', '.');
@endphp

<div {{ $attributes->class('kk-mh col-span-full') }}
     x-data="{
        n: 0,
        init() {
            const target = {{ $occupancy }};
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { this.n = target; return; }
            const dur = 950, t0 = performance.now();
            const tick = (now) => {
                const p = Math.min(1, (now - t0) / dur);
                this.n = Math.round((1 - Math.pow(1 - p, 3)) * target);
                if (p < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        }
     }">

    <p class="kk-mh-index">001 / Overzicht</p>

    <div class="kk-mh-grid">
        <div class="kk-mh-hero">
            <p class="kk-mh-eyebrow">Bezetting · deze maand</p>

            <p class="kk-mh-num">
                <span x-text="n">{{ $occupancy }}</span><span class="kk-mh-unit">%</span>
            </p>

            <span class="kk-mh-rule" aria-hidden="true"></span>

            <p class="kk-mh-context">
                {{ $rented }} van {{ $total }} koten verhuurd<span class="kk-mh-dot">·</span>{{ $available }} beschikbaar
            </p>

            <a href="{{ $manageUrl }}" class="kk-mh-cta">
                <span>Beheer koten</span>
                <span class="kk-mh-cta-chip" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </a>
        </div>

        {{-- Kerncijfers-paneel: vult de rechterkolom met de portfolio-cijfers. --}}
        <aside class="kk-mh-summary flex flex-col justify-center gap-6 rounded-[1.25rem] bg-[#e1e6ed] p-8">
            <div class="flex items-baseline justify-between gap-4">
                <p class="text-[0.625rem] font-semibold uppercase tracking-[0.14em] text-[#586573]">Omzet · /mnd</p>
                <p class="text-[clamp(1.75rem,2.4vw,2.5rem)] font-medium leading-none tracking-[-0.02em] tabular-nums text-[#0f1720]">€&thinsp;{{ $revFmt }}</p>
            </div>

            <div class="h-px w-full bg-[#0f17201f]"></div>

            <div class="flex items-baseline justify-between gap-4">
                <p class="text-[0.625rem] font-semibold uppercase tracking-[0.14em] text-[#586573]">Uitgelicht</p>
                <p class="text-[clamp(1.5rem,2vw,2rem)] font-medium leading-none tracking-[-0.02em] tabular-nums text-[#0f1720]">{{ $featured }}</p>
            </div>

            <div class="h-px w-full bg-[#0f17201f]"></div>

            <div class="flex items-baseline justify-between gap-4">
                <p class="text-[0.625rem] font-semibold uppercase tracking-[0.14em] text-[#586573]">Gebouwen</p>
                <p class="text-[clamp(1.5rem,2vw,2rem)] font-medium leading-none tracking-[-0.02em] tabular-nums text-[#0f1720]">{{ $buildings }}</p>
            </div>
        </aside>
    </div>
</div>
