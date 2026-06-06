@php
    use Filament\Support\Enums\Width;

    $livewire ??= null;

    $renderHookScopes = $livewire?->getRenderHookScopes();
    $maxContentWidth ??= (filament()->getSimplePageMaxContentWidth() ?? Width::Large);

    if (is_string($maxContentWidth)) {
        $maxContentWidth = Width::tryFrom($maxContentWidth) ?? $maxContentWidth;
    }
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    @props([
        'after' => null,
        'heading' => null,
        'subheading' => null,
    ])

    <div class="fi-simple-layout kk-auth">
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_START, scopes: $renderHookScopes) }}

        {{-- Animated background: a sparse drifting node network — "kotten" as points,
             thin links between nearby ones, on deep navy. Slow + restrained so the white
             type stays dominant. A few nodes carry the pumpkin accent. --}}
        <canvas class="kk-bg" aria-hidden="true"></canvas>
        <script>
            (() => {
                const c = document.currentScript.previousElementSibling;
                if (!c || !c.getContext) return;
                const ctx = c.getContext('2d');
                const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
                const NAVY = '#001f3d';
                const ACCENT = '255, 103, 0';   // pumpkin — a handful of highlighted kotten
                let w, h, dpr, pts, link;

                const build = () => {
                    // sparse: density scaled to area, capped — refined, not a screensaver
                    const target = Math.min(44, Math.round((innerWidth * innerHeight) / 38000));
                    link = Math.min(w, h) * 0.2;            // connect radius — sparse: only nearby kotten link up
                    pts = [];
                    for (let i = 0; i < target; i++) {
                        pts.push({
                            x: Math.random() * w,
                            y: Math.random() * h,
                            vx: (Math.random() - 0.5) * 0.055 * dpr,
                            vy: (Math.random() - 0.5) * 0.055 * dpr,
                            r: (Math.random() * 1.4 + 0.8) * dpr,
                            accent: Math.random() < 0.06,   // ~6% accent nodes
                        });
                    }
                };
                const resize = () => {
                    dpr = Math.min(window.devicePixelRatio || 1, 2);
                    w = c.width = innerWidth * dpr;
                    h = c.height = innerHeight * dpr;
                    c.style.width = innerWidth + 'px';
                    c.style.height = innerHeight + 'px';
                    build();
                };
                resize();
                addEventListener('resize', resize, { passive: true });

                const draw = () => {
                    ctx.fillStyle = NAVY;
                    ctx.fillRect(0, 0, w, h);
                    // links first, behind the dots
                    ctx.lineWidth = dpr * 0.6;
                    for (let i = 0; i < pts.length; i++) {
                        for (let j = i + 1; j < pts.length; j++) {
                            const dx = pts[i].x - pts[j].x, dy = pts[i].y - pts[j].y;
                            const d = Math.hypot(dx, dy);
                            if (d < link) {
                                ctx.strokeStyle = 'rgba(150, 184, 224, ' + (1 - d / link) * 0.12 + ')';
                                ctx.beginPath();
                                ctx.moveTo(pts[i].x, pts[i].y);
                                ctx.lineTo(pts[j].x, pts[j].y);
                                ctx.stroke();
                            }
                        }
                    }
                    // dots — a few accent "kotten" carry a faint halo
                    for (const p of pts) {
                        if (p.accent) {
                            ctx.beginPath();
                            ctx.arc(p.x, p.y, p.r * 3, 0, Math.PI * 2);
                            ctx.fillStyle = 'rgba(' + ACCENT + ', 0.04)';
                            ctx.fill();
                        }
                        ctx.beginPath();
                        ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                        ctx.fillStyle = p.accent ? 'rgba(' + ACCENT + ', 0.45)' : 'rgba(214, 228, 246, 0.35)';
                        ctx.fill();
                    }
                };
                const step = () => {
                    for (const p of pts) {
                        p.x += p.vx; p.y += p.vy;
                        if (p.x < 0 || p.x > w) p.vx *= -1;
                        if (p.y < 0 || p.y > h) p.vy *= -1;
                    }
                    draw();
                    requestAnimationFrame(step);
                };
                if (reduce) { draw(); return; }
                step();
            })();
        </script>


        {{-- Fixed editorial header — brand left --}}
        <header class="fixed inset-x-0 top-0 z-10 flex items-start justify-between gap-4 px-[clamp(1.5rem,4vw,3.5rem)] py-[clamp(1rem,2.25vw,1rem)]">
            <span class="inline-flex"><img src="{{ asset('/img/400pxX100pxWoordLogoLiggendZwart.png') }}" alt="KotKompas" class="h-18 w-auto [filter:brightness(0)_invert(1)]" /></span>
        </header>

        <div class="fi-simple-main-ctn kk-stage">
            @if (($hasTopbar ?? true) && filament()->auth()->check())
                <div class="fi-simple-layout-header">
                    @if (filament()->hasDatabaseNotifications())
                        @livewire(filament()->getDatabaseNotificationsLivewireComponent(), [
                            'lazy' => filament()->hasLazyLoadedDatabaseNotifications(),
                            'position' => \Filament\Enums\DatabaseNotificationsPosition::Topbar,
                        ])
                    @endif

                    @if (filament()->hasUserMenu())
                        @livewire(Filament\Livewire\SimpleUserMenu::class)
                    @endif
                </div>
            @endif

            <main class="fi-simple-main">
                {{ $slot }}
            </main>

            <aside class="max-md:hidden shrink-0 self-start max-w-60 mr-[6vw] pt-[0.4rem] text-justify font-sans">
                <p lang="nl" class="hyphens-auto text-[0.85rem] leading-normal tracking-[-0.015em] text-white">Vind je kot, regel het contract en volg alles op vanaf één plek. rechtstreeks met de eigenaar, zonder makelaarskosten of tussenpersoon. Bekijk beschikbare panden in jouw stad, vergelijk prijzen en voorzieningen, en plan een bezichtiging op een moment dat jou past, allemaal binnen dezelfde omgeving. Onderteken je huurovereenkomst digitaal, beheer je waarborg veilig en houd elke betaling en communicatie overzichtelijk bij zonder gedoe met losse documenten of verspreide e-mails. Of je nu op zoek bent naar een studentenkamer, een studio of een appartement, je behoudt altijd de volledige controle en transparantie — van de eerste zoekopdracht tot de dag dat je de sleutels in handen krijgt en daarna</p>
            </aside>
        </div>

        {{-- The giant switch word is emitted by Filament inside the form panel (subheading).
             Lift it to be a sibling of the panel so the translucent panel (z-index:2) can sit
             OVER it (a child can't paint behind its parent's own background). It's position:fixed,
             so moving it doesn't shift it on screen. --}}
        <script>
            (() => {
                const stage = document.querySelector('.kk-stage');
                if (!stage) return;

                const relocate = () => {
                    // Lift the giant switch word out so the panel (z-index:2) paints over it.
                    // Each Livewire morph re-emits a fresh word inside the panel; keep ONE canonical
                    // node in the stage and drop the morph copies — otherwise they stack, doubling
                    // the hover/entrance animation.
                    const words = [...document.querySelectorAll('.kk-switch')];
                    const keep = words.find((w) => w.parentElement === stage) || words[0];
                    if (keep) {
                        words.forEach((w) => { if (w !== keep) w.remove(); });
                        if (keep.parentElement !== stage) stage.appendChild(keep);
                    }

                    // login: move "wachtwoord vergeten?" beside the submit so they share one row
                    const forgot = document.querySelector('.kk-forgot');
                    const actions = document.querySelector('.fi-sc-actions');
                    if (forgot && actions && forgot.parentElement !== actions) actions.appendChild(forgot);
                };

                relocate();
                // Livewire re-renders (typing / validation) re-inject these nodes inside the
                // panel — without this the switch word jumps on top of the fields. Re-run on morph.
                new MutationObserver(relocate).observe(stage, { childList: true, subtree: true });
            })();
        </script>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::FOOTER, scopes: $renderHookScopes) }}

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_END, scopes: $renderHookScopes) }}
    </div>
</x-filament-panels::layout.base>
