/**
 * The GSAP namespace, resolved lazily so the library stays out of the
 * main bundle exactly as the Animations chunk does.
 */
type Gsap = typeof import('gsap')['gsap'];

/**
 * Which attributes a swapping section is built from.
 */
export interface SectionSwapOptions {
    /** Attribute selector identifying the section in both documents. */
    root: string;
    /** Attribute selector for the chips that trigger a swap. */
    chip: string;
    /** Attribute selector for the children that stagger in behind it. */
    stagger: string;
}

/**
 * Section Swap Component
 *
 * Replaces one section of a page when a chip inside it is clicked,
 * leaving the rest of the page untouched. The chips remain ordinary
 * links, so without JavaScript each one is a normal full page
 * navigation.
 *
 * The swap is cross-faded with GSAP: the outgoing section lifts away
 * while the replacement is fetched, then the incoming section - chips
 * and active state included, since they live inside it - fades up with
 * its rows staggering in behind it.
 *
 * Used by the 헌금 week picker and the 자료실 tabs, which differ only in
 * the attributes they are built from.
 */
export class SectionSwap {
    private section: HTMLElement;

    private options: SectionSwapOptions;

    /**
     * Identifies the most recent swap. Every awaited step re-checks it,
     * so a rapid second click abandons the first swap before it can
     * touch the DOM rather than leaving a half-animated section.
     */
    private latestSwap = 0;

    /**
     * Creates a new SectionSwap instance.
     *
     * @param section - The element carrying the root attribute
     * @param options - The attributes this section is built from
     */
    constructor(section: HTMLElement, options: SectionSwapOptions) {
        this.section = section;
        this.options = options;

        /**
         * Clicks are handled on the document rather than on each chip so
         * that a swapped-in set of chips needs no rebinding.
         */
        document.addEventListener('click', (event: MouseEvent) => this.onClick(event));
        window.addEventListener('popstate', () => void this.swap(window.location.href, false));
    }

    /**
     * Intercepts a plain left click on a chip inside the section.
     *
     * @param event - The originating click event
     */
    private onClick(event: MouseEvent): void {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        const chip = (event.target as Element | null)?.closest<HTMLAnchorElement>(this.options.chip);

        if (! chip || ! this.section.contains(chip)) {
            return;
        }

        event.preventDefault();
        void this.swap(chip.href, true);
    }

    /**
     * Loads GSAP on demand, or nothing at all when the visitor prefers
     * reduced motion - in which case the swap stays instant.
     *
     * @returns The GSAP namespace, or null when motion is unwanted
     */
    private async motion(): Promise<Gsap | null> {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return null;
        }

        const { gsap } = await import('gsap');

        return gsap;
    }

    /**
     * Fetches a URL and replaces the section with the one it carries.
     *
     * @param url - The URL to render
     * @param push - Whether to add a history entry for it
     */
    private async swap(url: string, push: boolean): Promise<void> {
        const swapId = ++this.latestSwap;

        try {
            /** The request runs alongside the exit animation, not after it. */
            const request = fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            const gsap = await this.motion();

            if (gsap) {
                gsap.killTweensOf(this.section);
                await gsap.to(this.section, { opacity: 0, y: -12, duration: 0.25, ease: 'power2.in' });
            }

            const response = await request;

            if (! response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const parsed = new DOMParser().parseFromString(await response.text(), 'text/html');
            const incoming = parsed.querySelector<HTMLElement>(this.options.root);

            if (! incoming) {
                throw new Error('Missing section');
            }

            if (swapId !== this.latestSwap) {
                return;
            }

            this.section.replaceWith(incoming);
            this.section = incoming;

            if (push) {
                window.history.pushState({}, '', url);
            }

            gsap?.timeline()
                .fromTo(incoming, { opacity: 0, y: 16 }, { opacity: 1, y: 0, duration: 0.45, ease: 'power2.out', clearProps: 'opacity,transform' })
                .fromTo(
                    incoming.querySelectorAll(this.options.stagger),
                    { opacity: 0, y: 12 },
                    { opacity: 1, y: 0, duration: 0.4, ease: 'power2.out', stagger: 0.08, clearProps: 'opacity,transform' },
                    '-=0.3',
                );
        } catch {
            /**
             * Anything unexpected falls back to the plain navigation the
             * chip would have performed on its own.
             */
            window.location.href = url;
        }
    }
}
