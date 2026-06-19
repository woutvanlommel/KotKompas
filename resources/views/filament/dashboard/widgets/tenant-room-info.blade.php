@php
    $typeLabels = [
        'kamer'       => 'Kamer',
        'studio'      => 'Studio',
        'appartement' => 'Appartement',
    ];
@endphp

<div {{ $attributes->class('kk-mh kk-mh--tenant col-span-full') }}>
    <p class="kk-mh-index">001 / Mijn kot</p>

    @if ($rooms->isEmpty())
        <div class="kk-mh-hero kk-mh-hero--empty">
            <p class="kk-mh-eyebrow">Nog geen kot gekoppeld</p>
            <p class="kk-mh-title kk-mh-title--muted">Je kot verschijnt hier</p>
            <span class="kk-mh-rule" aria-hidden="true"></span>
            <p class="kk-mh-context">
                Zodra je verhuurder je aan een kot koppelt, zie je hier al je huur-, gebouw- en contactinfo op één plek.
            </p>
            <a href="{{ $browseUrl }}" class="kk-mh-cta">
                <span>Bekijk beschikbare koten</span>
                <span class="kk-mh-cta-chip" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </span>
            </a>
        </div>
    @else
        @foreach ($rooms as $room)
            @php
                $cover = $room->getFirstMediaUrl('cover', 'thumb');
                $isRented = $room->status === 'rented';
            @endphp
            <div class="kk-mh-grid @if (! $loop->first) kk-mh-grid--stacked @endif">
                <div class="kk-mh-hero">
                    <p class="kk-mh-status">
                        <span class="kk-mh-badge @if ($isRented) kk-mh-badge--active @endif">
                            {{ $isRented ? 'Verhuurd aan jou' : ucfirst($room->status) }}
                        </span>
                    </p>

                    <h2 class="kk-mh-title">{{ $room->title }}</h2>

                    <span class="kk-mh-rule" aria-hidden="true"></span>

                    <p class="kk-mh-context">
                        {{ $room->building->name }}<span class="kk-mh-dot">·</span>{{ $room->full_address }}
                    </p>

                    <dl class="kk-mh-rail">
                        <div>
                            <dt class="kk-mh-eyebrow">Huur · /mnd</dt>
                            <dd class="kk-mh-stat">€&thinsp;{{ number_format($room->total_monthly_price, 0, ',', '.') }}</dd>
                        </div>
                        <div>
                            <dt class="kk-mh-eyebrow">Type</dt>
                            <dd class="kk-mh-stat">{{ $typeLabels[$room->type] ?? $room->type }}</dd>
                        </div>
                        <div>
                            <dt class="kk-mh-eyebrow">Oppervlakte</dt>
                            <dd class="kk-mh-stat">{{ $room->surface_m2 ? $room->surface_m2 . ' m²' : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="kk-mh-eyebrow">Waarborg</dt>
                            <dd class="kk-mh-stat">@if ($room->deposit_amount)€&thinsp;{{ number_format((float) $room->deposit_amount, 0, ',', '.') }}@else—@endif</dd>
                        </div>
                    </dl>

                    <a href="{{ $documentsUrl }}" class="kk-mh-cta">
                        <span>Mijn documenten</span>
                        <span class="kk-mh-cta-chip" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </span>
                    </a>
                </div>

                <figure class="kk-mh-figure kk-mh-figure--empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" aria-hidden="true">
                        <path d="M3 10.5 12 4l9 6.5M5 9.5V20h14V9.5M9.5 20v-6h5v6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    @if ($cover)
                        <img src="{{ $cover }}" alt="Foto van {{ $room->title }}" loading="lazy" onerror="this.remove()">
                    @endif
                </figure>
            </div>
        @endforeach
    @endif
</div>
