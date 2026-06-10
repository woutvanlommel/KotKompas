import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';
import Lenis from 'lenis';
import { initSearchSuggest } from './search-suggest';

gsap.registerPlugin(ScrollTrigger, SplitText);

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// First page of the session? On later same-session navigations we SKIP the entrance
// animation so the cross-document view-transition crossfade reads as one smooth motion.
const firstVisit = ((): boolean => {
    try {
        const seen = sessionStorage.getItem('kk-seen');
        sessionStorage.setItem('kk-seen', '1');
        return !seen;
    } catch {
        return true;
    }
})();

/* ---- Lenis smooth scroll (skipped under reduced-motion) ---- */
if (!reduceMotion) {
    const lenis = new Lenis({ duration: 1.1, smoothWheel: true });
    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.add((time: number) => lenis.raf(time * 1000));
    gsap.ticker.lagSmoothing(0);

    // Anchor links → smooth scroll via Lenis
    document.querySelectorAll<HTMLAnchorElement>('a[href^="#"]').forEach((a) => {
        a.addEventListener('click', (e) => {
            const target = document.querySelector(a.getAttribute('href') ?? '');
            if (target) {
                e.preventDefault();
                lenis.scrollTo(target as HTMLElement, { offset: -72 });
            }
        });
    });

    // Scroll-velocity skew — subtle (trust site → tight ±5° clamp). Transform-only.
    const clampSkew = gsap.utils.clamp(-5, 5);
    const skewTargets = gsap.utils.toArray<HTMLElement>('[data-skew]');
    if (skewTargets.length) {
        let resetTimer = 0;
        lenis.on('scroll', ({ velocity }: { velocity: number }) => {
            gsap.to(skewTargets, { skewY: clampSkew(velocity * 0.25), duration: 0.3, ease: 'power3', overwrite: true });
            clearTimeout(resetTimer);
            resetTimer = window.setTimeout(() => {
                gsap.to(skewTargets, { skewY: 0, duration: 0.5, ease: 'power3', overwrite: true });
            }, 120);
        });
    }
}

/* ---- Scroll parallax (always on for motion users — a scroll effect, not an entrance) ---- */
function runParallax(): void {
    gsap.utils.toArray<HTMLElement>('[data-parallax]').forEach((el) => {
        gsap.fromTo(
            el,
            { yPercent: -8 },
            {
                yPercent: 8,
                ease: 'none',
                scrollTrigger: { trigger: el.parentElement ?? el, start: 'top bottom', end: 'bottom top', scrub: true },
            },
        );
    });
}

/* ---- Reveal animations ---- */
function initReveals(): void {
    // No entrance under reduced-motion OR on an intra-site navigation (a page already seen
    // this session) — show content instantly so the page crossfade reads as one smooth motion.
    if (reduceMotion || !firstVisit) {
        gsap.set('[data-reveal], [data-split], [data-converge]', { opacity: 1, x: 0, y: 0 });
        gsap.utils.toArray<HTMLElement>('[data-reveal-stagger]').forEach((g) => gsap.set(g.children, { opacity: 1, x: 0, y: 0 }));
        if (!reduceMotion) runParallax();
        return;
    }

    // Clip-line headline reveal
    document.querySelectorAll<HTMLElement>('[data-split]').forEach((el) => {
        const split = new SplitText(el, { type: 'lines', linesClass: 'split-line' });
        const wraps: HTMLElement[] = [];
        split.lines.forEach((line) => {
            const wrap = document.createElement('span');
            // padding + negative margin so overflow:hidden clips the reveal WITHOUT
            // cutting ascenders/descenders (j, g) at lh < 1.
            wrap.style.cssText = 'display:block;overflow:hidden;padding-block:0.12em;margin-block:-0.12em;';
            line.parentNode?.insertBefore(wrap, line);
            wrap.appendChild(line);
            wraps.push(wrap);
        });
        gsap.from(split.lines, {
            yPercent: 115,
            duration: 0.9,
            stagger: 0.08,
            ease: 'expo.out',
            delay: 0.05,
            onComplete: () => wraps.forEach((w) => (w.style.overflow = 'visible')),
        });
    });

    // Scroll-triggered fade-up reveals
    gsap.utils.toArray<HTMLElement>('[data-reveal]').forEach((el) => {
        gsap.from(el, {
            opacity: 0,
            y: 24,
            duration: 0.8,
            ease: 'expo.out',
            scrollTrigger: { trigger: el, start: 'top 85%' },
        });
    });

    // Staggered children (e.g. koten grid)
    gsap.utils.toArray<HTMLElement>('[data-reveal-stagger]').forEach((group) => {
        gsap.from(group.children, {
            opacity: 0,
            y: 28,
            duration: 0.7,
            stagger: 0.07,
            ease: 'expo.out',
            scrollTrigger: { trigger: group, start: 'top 85%' },
        });
    });

    // Convergence — the two sides (huurder ← → verhuurder) slide in to meet.
    gsap.utils.toArray<HTMLElement>('[data-converge]').forEach((el) => {
        const fromX = el.dataset.converge === 'right' ? 70 : -70;
        gsap.from(el, {
            x: fromX,
            opacity: 0,
            duration: 1,
            ease: 'expo.out',
            scrollTrigger: { trigger: el, start: 'top 82%' },
        });
    });

    // Scroll parallax (transform-only, GPU).
    runParallax();

    // Hero: kinetic wordmark lines rise in; floating live card settles + scroll-drifts.
    const heroMark = document.querySelector<HTMLElement>('.kk-hero-mark');
    if (heroMark) {
        gsap.from(gsap.utils.toArray<HTMLElement>('[data-hero-w]'), {
            yPercent: 118,
            duration: 1,
            stagger: 0.1,
            ease: 'expo.out',
            delay: 0.08,
        });
        const card = document.querySelector<HTMLElement>('[data-hero-card]');
        if (card) {
            // Only translate/opacity in GSAP — the resting tilt stays a CSS `rotate`.
            gsap.from(card, { yPercent: 18, opacity: 0, duration: 0.9, ease: 'expo.out', delay: 0.5 });
            gsap.to(card, {
                yPercent: -14,
                ease: 'none',
                scrollTrigger: { trigger: '.kk-hero', start: 'top top', end: 'bottom top', scrub: true },
            });
        }
    }

    // Featured koten: scroll card-stack. Pinning is done with CSS `position: sticky`
    // (set via .is-stacking), NOT ScrollTrigger pin — the robust way under Lenis.
    const stack = document.querySelector<HTMLElement>('[data-koten-stack]');
    if (stack) {
        const items = gsap.utils.toArray<HTMLElement>('[data-koten-card]', stack);
        // Stack only on tablet/desktop — on phones the sticky-scale is janky; fall back to column.
        if (items.length > 1 && window.innerWidth >= 768) {
            stack.classList.add('is-stacking');
            const last = items[items.length - 1];
            items.forEach((item, i) => {
                if (i === items.length - 1) return;
                const inner = item.querySelector<HTMLElement>('.kk-stack-inner');
                if (!inner) return;
                const targetScale = Math.max(0.86, 1 - (items.length - 1 - i) * 0.04);
                gsap.fromTo(
                    inner,
                    { scale: 1, rotate: 0 },
                    {
                        scale: targetScale,
                        rotate: i % 2 === 0 ? -1.5 : 1.5,
                        ease: 'none',
                        scrollTrigger: {
                            trigger: item,
                            start: 'top 14%',
                            endTrigger: last,
                            end: 'top 14%',
                            scrub: true,
                            invalidateOnRefresh: true,
                        },
                    },
                );
            });
            // Cards are lazy-loaded → height is 0 until paint; recompute scrub lengths once loaded.
            const imgs = stack.querySelectorAll('img');
            let pending = imgs.length;
            const ping = (): void => { if (--pending <= 0) ScrollTrigger.refresh(); };
            if (imgs.length === 0) {
                ScrollTrigger.refresh();
            } else {
                imgs.forEach((img) => {
                    if (img.complete) ping();
                    else {
                        img.addEventListener('load', ping, { once: true });
                        img.addEventListener('error', ping, { once: true });
                    }
                });
            }
        }
    }
}

/* ---- Adaptive nav: solidify once scrolled past the dark hero ---- */
const adaptiveNav = document.querySelector<HTMLElement>('.kk-nav[data-adaptive]');
if (adaptiveNav) {
    const onScroll = (): void => {
        adaptiveNav.classList.toggle('is-solid', window.scrollY > window.innerHeight * 0.7);
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
}

if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(initReveals);
} else {
    window.addEventListener('load', initReveals);
}

/* ---- Magnetic CTAs + pointer-following card label (desktop fine-pointer only) ---- */
const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
if (finePointer && !reduceMotion) {
    // Magnetic pull on primary CTAs
    gsap.utils.toArray<HTMLElement>('[data-magnetic]').forEach((el) => {
        const strength = parseFloat(el.dataset.magnetic || '0.3');
        const xTo = gsap.quickTo(el, 'x', { duration: 0.5, ease: 'power3' });
        const yTo = gsap.quickTo(el, 'y', { duration: 0.5, ease: 'power3' });
        el.addEventListener('pointermove', (e) => {
            const r = el.getBoundingClientRect();
            xTo((e.clientX - (r.left + r.width / 2)) * strength);
            yTo((e.clientY - (r.top + r.height / 2)) * strength);
        });
        el.addEventListener('pointerleave', () => {
            gsap.to(el, { x: 0, y: 0, duration: 0.6, ease: 'elastic.out(1, 0.4)' });
        });
    });

    // Pointer-following "Bekijk →" label over koten cards
    const cards = gsap.utils.toArray<HTMLElement>('[data-card-cursor]');
    if (cards.length) {
        const label = document.createElement('div');
        label.className = 'kk-card-cursor';
        label.setAttribute('aria-hidden', 'true');
        label.innerHTML = '<span>Bekijk</span><svg viewBox="0 0 16 16" fill="none"><path d="M4 12L12 4M12 4H6M12 4V10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        document.body.appendChild(label);
        const xTo = gsap.quickTo(label, 'x', { duration: 0.5, ease: 'power3' });
        const yTo = gsap.quickTo(label, 'y', { duration: 0.5, ease: 'power3' });
        gsap.set(label, { xPercent: -50, yPercent: -50, scale: 0, autoAlpha: 0 });
        let active = 0;
        const move = (e: PointerEvent): void => { xTo(e.clientX); yTo(e.clientY); };
        cards.forEach((card) => {
            card.addEventListener('pointerenter', (e) => {
                active++;
                xTo(e.clientX); yTo(e.clientY);
                gsap.to(label, { scale: 1, autoAlpha: 1, duration: 0.3, ease: 'power3' });
                window.addEventListener('pointermove', move);
            });
            card.addEventListener('pointerleave', () => {
                active = Math.max(0, active - 1);
                if (active === 0) {
                    gsap.to(label, { scale: 0, autoAlpha: 0, duration: 0.25, ease: 'power3' });
                    window.removeEventListener('pointermove', move);
                }
            });
        });
    }
}

/* ---- Editorial nav: solidify + auto-hide on scroll, fullscreen mobile overlay ---- */
const topnav = document.querySelector<HTMLElement>('.kk-topnav');
if (topnav) {
    const overHero = topnav.hasAttribute('data-over-hero');
    let lastY = window.scrollY;
    const onNavScroll = (): void => {
        const y = window.scrollY;
        const solidAfter = overHero ? window.innerHeight * 0.75 : 32;
        topnav.classList.toggle('is-solid', y > solidAfter);
        if (y > 140 && y > lastY + 4) topnav.classList.add('is-hidden');
        else if (y < lastY - 4 || y < 140) topnav.classList.remove('is-hidden');
        lastY = y;
    };
    onNavScroll();
    window.addEventListener('scroll', onNavScroll, { passive: true });

    // Fullscreen mobile overlay
    const toggle = topnav.querySelector<HTMLButtonElement>('.kk-menu-toggle');
    const overlay = document.getElementById('kk-menu-overlay');
    if (toggle && overlay) {
        const closeMenu = (): void => {
            overlay.classList.remove('is-open');
            overlay.setAttribute('hidden', '');
            toggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        };
        const openMenu = (): void => {
            overlay.removeAttribute('hidden');
            requestAnimationFrame(() => overlay.classList.add('is-open'));
            toggle.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        };
        toggle.addEventListener('click', () => (overlay.classList.contains('is-open') ? closeMenu() : openMenu()));
        overlay.querySelector('.kk-menu-close')?.addEventListener('click', closeMenu);
        overlay.querySelectorAll('a').forEach((a) => a.addEventListener('click', closeMenu));
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeMenu(); });
    }
}

/* ---- FAQ live search — filters questions by keyword, hides empty categories ---- */
const faqSearch = document.querySelector<HTMLInputElement>('[data-faq-search]');
if (faqSearch) {
    const items = Array.from(document.querySelectorAll<HTMLElement>('[data-faq-item]'));
    const cats = Array.from(document.querySelectorAll<HTMLElement>('[data-faq-cat]'));
    const emptyMsg = document.querySelector<HTMLElement>('[data-faq-empty]');
    faqSearch.addEventListener('input', () => {
        const q = faqSearch.value.trim().toLowerCase();
        let anyShown = false;
        items.forEach((it) => {
            const show = !q || (it.dataset.q || '').includes(q);
            it.hidden = !show;
            if (show) anyShown = true;
        });
        cats.forEach((cat) => {
            cat.hidden = !Array.from(cat.querySelectorAll<HTMLElement>('[data-faq-item]')).some((i) => !i.hidden);
        });
        if (emptyMsg) emptyMsg.hidden = anyShown || !q;
    });
}

/* ---- Zoeksuggesties op home-hero en kotenpagina ---- */
initSearchSuggest();

/* ---- Filterpaneel-toggle op mobiel (kotenpagina) ---- */
const filtersToggle = document.querySelector<HTMLButtonElement>('[data-filters-toggle]');
const filtersPanel = document.querySelector<HTMLElement>('[data-filters-panel]');
if (filtersToggle && filtersPanel) {
    filtersToggle.addEventListener('click', () => {
        const collapsed = filtersPanel.toggleAttribute('data-collapsed');
        filtersToggle.setAttribute('aria-expanded', String(!collapsed));
    });
}
