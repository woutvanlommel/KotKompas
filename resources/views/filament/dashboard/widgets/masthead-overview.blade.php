@php
    $scoreFmt = $score !== null ? number_format((float) $score, 1, ',', '.') : '—';
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

            <dl class="kk-mh-rail">
                <div>
                    <dt class="kk-mh-eyebrow">Omzet · /mnd</dt>
                    <dd class="kk-mh-stat">€&thinsp;{{ $revFmt }}</dd>
                </div>
                <div>
                    <dt class="kk-mh-eyebrow">Uitgelicht</dt>
                    <dd class="kk-mh-stat">{{ $featured }}</dd>
                </div>
                <div>
                    <dt class="kk-mh-eyebrow">Gebouwen</dt>
                    <dd class="kk-mh-stat">{{ $buildings }}</dd>
                </div>
            </dl>

            <a href="{{ $manageUrl }}" class="kk-mh-cta">
                <span>Beheer koten</span>
                <span class="kk-mh-cta-chip" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </a>
        </div>

        <aside class="kk-mh-score">
            <p class="kk-mh-eyebrow">Kotscore · gemiddeld</p>
            <p class="kk-mh-score-num">{{ $scoreFmt }}<span class="kk-mh-score-max">/5</span></p>
            <p class="kk-mh-score-meta">{{ $reviews }} {{ $reviews === 1 ? 'beoordeling' : 'beoordelingen' }}</p>
        </aside>
    </div>
</div>
