/**
 * Photo Carousel Component
 *
 * Horizontal carousel with two ends: native scroll-snap handles touch
 * swipes and trackpads, and the circular arrow buttons page through one
 * card at a time until the band runs out, at which point the arrow for
 * that direction goes dim and stops responding. Slides the page held
 * back for performance are adopted from an inert template once loading
 * finishes. Without JavaScript the band is a plain scrollable row of the
 * photographs present in the HTML.
 */
export class PhotoSlider {
    private track: HTMLElement | null;
    private prev: HTMLButtonElement | null;
    private next: HTMLButtonElement | null;

    /**
     * Creates a new PhotoSlider instance.
     *
     * @param container - Element carrying the data-photo-slider attribute
     */
    constructor(container: HTMLElement) {
        this.track = container.querySelector<HTMLElement>('[data-slider-track]');
        this.prev = container.querySelector<HTMLButtonElement>('[data-slider-prev]');
        this.next = container.querySelector<HTMLButtonElement>('[data-slider-next]');

        if (!this.track) {
            return;
        }

        this.hydrateDeferred(container.querySelector<HTMLTemplateElement>('[data-slider-deferred]'));

        this.track.addEventListener('scroll', () => this.syncArrows(), { passive: true });
        window.addEventListener('resize', () => this.syncArrows());

        this.prev?.addEventListener('click', () => this.page(-1));
        this.next?.addEventListener('click', () => this.page(1));

        this.syncArrows();
    }

    /**
     * Moves the deferred slides into the track once they are worth fetching.
     *
     * Their markup travels inside an inert <template>, so the browser never
     * requests those photographs while the hero image is still competing for
     * the connection. They are adopted once the page has finished loading and
     * the band is within a couple of screens of the viewport, which both keeps
     * them out of the initial load and spares the data of a visitor who never
     * scrolls this far. They join the end of the track, so nothing already on
     * screen moves and the scroll position needs no correcting.
     *
     * @param template - Element holding the slides held back from the initial HTML
     */
    private hydrateDeferred(template: HTMLTemplateElement | null): void {
        if (!template || template.content.children.length === 0) {
            return;
        }

        const adopt = (): void => {
            this.track!.appendChild(template.content);
            this.syncArrows();
        };

        /** Watching only starts after load so a tall screen cannot pull the photos forward. */
        const watch = (): void => {
            if (!('IntersectionObserver' in window)) {
                adopt();

                return;
            }

            const observer = new IntersectionObserver(
                (entries) => {
                    if (entries.some((entry) => entry.isIntersecting)) {
                        observer.disconnect();
                        adopt();
                    }
                },
                { rootMargin: '200% 0px' },
            );

            observer.observe(this.track!);
        };

        if (document.readyState === 'complete') {
            watch();
        } else {
            window.addEventListener('load', watch, { once: true });
        }
    }

    /**
     * Dims the arrow that has nothing left to reach.
     *
     * A band that fits on screen has both arrows off, so a set of three
     * photographs does not offer a control that would do nothing. The
     * tolerance absorbs the sub-pixel remainder a fractional card width
     * leaves behind, which would otherwise keep the forward arrow live
     * at the very end.
     */
    private syncArrows(): void {
        const track = this.track!;
        const furthest = track.scrollWidth - track.clientWidth;

        if (this.prev) {
            this.prev.disabled = track.scrollLeft <= 1;
        }

        if (this.next) {
            this.next.disabled = track.scrollLeft >= furthest - 1;
        }
    }

    /**
     * Pages by exactly one card - the same distance as a light swipe.
     *
     * The scroll container clamps at both ends on its own, so a page
     * past the last card simply lands on it.
     *
     * @param direction - -1 for backwards, +1 for forwards
     */
    private page(direction: number): void {
        const track = this.track!;
        const card = track.firstElementChild as HTMLElement | null;
        const step = card ? card.offsetWidth + 16 : track.clientWidth * 0.85;

        track.scrollBy({
            left: direction * step,
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
        });
    }
}
