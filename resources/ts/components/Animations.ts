import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

/**
 * Animations Component
 *
 * Site-wide motion built on GSAP: a staggered hero introduction, gentle
 * scroll-triggered reveals for every section, and a staggered slide-in
 * for the mobile menu links. All animation is skipped entirely when the
 * visitor prefers reduced motion.
 */
export class Animations {
    /**
     * Creates the animation layer for the current page.
     */
    constructor() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        gsap.registerPlugin(ScrollTrigger);
        this.animateHero();
        this.animateSections();
        this.bindMobileMenu();
    }

    /**
     * Staggers the hero copy upwards as the page loads.
     */
    private animateHero(): void {
        const items = document.querySelectorAll('[data-hero-item]');
        if (items.length === 0) {
            return;
        }

        gsap.from(items, {
            y: 28,
            opacity: 0,
            duration: 0.8,
            ease: 'power3.out',
            stagger: 0.12,
        });
    }

    /**
     * Fades each section up as it scrolls into view, once only.
     *
     * Each section is triggered on its own, so bands that share a
     * background colour belong in one section - two of them would
     * travel separately and show the page background in between.
     */
    private animateSections(): void {
        gsap.utils.toArray<HTMLElement>('main > section:not(:first-child)').forEach((section) => {
            gsap.from(section, {
                y: 32,
                opacity: 0,
                duration: 0.7,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: section,
                    start: 'top 88%',
                    once: true,
                },
            });
        });
    }

    /**
     * Slides the mobile menu links in whenever the menu opens.
     */
    private bindMobileMenu(): void {
        document.addEventListener('mobilenav:opened', () => {
            gsap.from('[data-mobile-nav-menu] a', {
                x: 32,
                opacity: 0,
                duration: 0.4,
                ease: 'power2.out',
                stagger: 0.05,
                clearProps: 'all',
            });
        });
    }
}
