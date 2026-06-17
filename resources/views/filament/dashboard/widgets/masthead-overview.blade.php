@php
    $revFmt = number_format($revenue, 0, ',', '.');
    $potFmt = number_format($potential, 0, ',', '.');
    $yearFmt = number_format($yearly, 0, ',', '.');
    $newRevFmt = number_format($newRevenue, 0, ',', '.');
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

    <p class="kk-mh-greeting">{{ $greeting }}, {{ $firstName }}</p>
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

            @if ($newRentals > 0)
                <p class="kk-mh-delta kk-mh-delta--up">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M7 17 17 7M17 7H9M17 7v8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    +{{ $newRentals }} {{ $newRentals === 1 ? 'kot' : 'koten' }} verhuurd deze maand
                </p>
            @endif

            <a href="{{ $manageUrl }}" class="kk-mh-cta">
                <span>Beheer koten</span>
                <span class="kk-mh-cta-chip" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </a>
        </div>

        {{-- Kerncijfers als tegels: elk getal met context (icoon + delta), Flux-patroon
             vertaald naar de brand-taal — geen verzonnen percentages. --}}
        <aside class="kk-mh-kpis" aria-label="Kerncijfers">
            <div class="kk-mh-kpi">
                <div class="kk-mh-kpi-head">
                    <span class="kk-mh-kpi-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <p class="kk-mh-kpi-label">Omzet · /mnd</p>
                </div>
                <p class="kk-mh-kpi-value">€&thinsp;{{ $revFmt }}</p>
                @if ($newRevenue > 0)
                    <span class="kk-mh-kpi-delta kk-mh-kpi-delta--up">↗ +€&thinsp;{{ $newRevFmt }} deze maand</span>
                @endif
            </div>

            <div class="kk-mh-kpi">
                <div class="kk-mh-kpi-head">
                    <span class="kk-mh-kpi-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 17 9 11l4 4 8-8M21 7v5M21 7h-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <p class="kk-mh-kpi-label">Potentieel · /mnd</p>
                </div>
                <p class="kk-mh-kpi-value">€&thinsp;{{ $potFmt }}</p>
            </div>

            <div class="kk-mh-kpi">
                <div class="kk-mh-kpi-head">
                    <span class="kk-mh-kpi-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 21V8l9-5 9 5v13M9 21v-6h6v6M3 21h18" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <p class="kk-mh-kpi-label">Op jaarbasis</p>
                </div>
                <p class="kk-mh-kpi-value">€&thinsp;{{ $yearFmt }}</p>
            </div>

            <div class="kk-mh-kpi">
                <div class="kk-mh-kpi-head">
                    <span class="kk-mh-kpi-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m12 3 2.6 5.3 5.9.9-4.2 4.1 1 5.8L12 16.9 6.7 19.7l1-5.8L3.5 9.7l5.9-.9z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <p class="kk-mh-kpi-label">Nieuwe reviews</p>
                </div>
                <p class="kk-mh-kpi-value">{{ $newReviews }}</p>
                @if ($newReviews > 0)
                    <span class="kk-mh-kpi-delta kk-mh-kpi-delta--up">deze maand</span>
                @endif
            </div>
        </aside>
    </div>
</div>
