import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';
import Lenis from 'lenis';

gsap.registerPlugin(ScrollTrigger, SplitText);

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

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
}

/* ---- Reveal animations ---- */
function initReveals(): void {
    if (reduceMotion) {
        gsap.set('[data-reveal], [data-split]', { opacity: 1, y: 0 });
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
