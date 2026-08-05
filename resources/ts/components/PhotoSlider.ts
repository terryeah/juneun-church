/**
 * Photo Carousel Component
 *
 * Apple-style horizontal carousel: native scroll-snap handles touch
 * swipes and trackpads, and the circular arrow buttons page through
 * one viewport at a time. The slide set is cloned once so the band
 * loops seamlessly - crossing into the cloned set teleports the
 * scroll position back by exactly one set width, which is invisible
 * because both sets render identically. Slides the page held back for
 * performance are adopted from an inert template once loading finishes.
 * Without JavaScript the band is a plain scrollable row of the photo
 * links present in the HTML.
 */
export class PhotoSlider {
    private track: HTMLElement | null;
    private setWidth: number = 0;

    /**
     * Creates a new PhotoSlider instance.
     *
     * @param container - Element carrying the data-photo-slider attribute
     */
    constructor(container: HTMLElement) {
        this.track = container.querySelector<HTMLElement>('[data-slider-track]');

        if (!this.track || this.track.children.length < 2) {
            return;
        }

        this.cloneSlides();
        this.measure();
        this.hydrateDeferred(container.querySelector<HTMLTemplateElement>('[data-slider-deferred]'));
        window.addEventListener('resize', () => this.measure());
        this.track.addEventListener('scroll', () => this.wrap(), { passive: true });

        container.querySelector('[data-slider-prev]')?.addEventListener('click', () => this.page(-1));
        container.querySelector('[data-slider-next]')?.addEventListener('click', () => this.page(1));
    }

    /**
     * Appends one hidden-from-AT copy of every slide for the loop.
     */
    private cloneSlides(): void {
        Array.from(this.track!.children).forEach((slide) => {
            const clone = slide.cloneNode(true) as HTMLElement;
            clone.dataset.sliderClone = '';
            clone.setAttribute('aria-hidden', 'true');
            clone.setAttribute('tabindex', '-1');
            clone.querySelectorAll('a').forEach((link) => link.setAttribute('tabindex', '-1'));
            this.track!.appendChild(clone);
        });
    }

    /**
     * Moves the deferred slides into the track once they are worth fetching.
     *
     * Their markup travels inside an inert <template>, so the browser never
     * requests those photographs while the hero image is still competing for
     * the connection. They are adopted once the page has finished loading and
     * the band is within a couple of screens of the viewport, which both keeps
     * them out of the initial load and spares the data of a visitor who never
     * scrolls this far. Adopting them invalidates the loop, which is measured
     * from the cloned set, so the clones are thrown away and rebuilt around
     * the full set. The scroll position is folded back into the original set
     * beforehand and restored afterwards, because the original slides stay at
     * the head of the track and so keep their offsets.
     *
     * @param template - Element holding the slides held back from the initial HTML
     */
    private hydrateDeferred(template: HTMLTemplateElement | null): void {
        if (!template || template.content.children.length === 0) {
            return;
        }

        const adopt = (): void => {
            const track = this.track!;
            const offset = this.setWidth > 0 ? track.scrollLeft % this.setWidth : track.scrollLeft;

            track.querySelectorAll('[data-slider-clone]').forEach((clone) => clone.remove());
            track.appendChild(template.content);
            this.cloneSlides();
            this.measure();
            this.jump(offset);
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
     * Measures the pixel width of one full slide set.
     */
    private measure(): void {
        const track = this.track!;
        const slides = track.children;
        const first = slides[0] as HTMLElement;
        const firstClone = slides[slides.length / 2] as HTMLElement;
        this.setWidth = firstClone.offsetLeft - first.offsetLeft;
    }

    /**
     * Teleports back by one set width once the cloned set is reached,
     * keeping the loop invisible.
     */
    private wrap(): void {
        const track = this.track!;

        if (this.setWidth > 0 && track.scrollLeft >= this.setWidth) {
            this.jump(track.scrollLeft - this.setWidth);
        }
    }

    /**
     * Moves the scroll position instantly, without smooth behaviour.
     */
    private jump(left: number): void {
        const track = this.track!;
        const previous = track.style.scrollBehavior;
        track.style.scrollBehavior = 'auto';
        track.scrollLeft = left;
        track.style.scrollBehavior = previous;
    }

    /**
     * Pages by exactly one card - the same distance as a light swipe -
     * wrapping at both ends.
     *
     * @param direction - -1 for backwards, +1 for forwards
     */
    private page(direction: number): void {
        const track = this.track!;

        /** Going backwards from the start borrows the cloned set first. */
        if (direction < 0 && track.scrollLeft <= 2 && this.setWidth > 0) {
            this.jump(track.scrollLeft + this.setWidth);
        }

        const card = track.firstElementChild as HTMLElement | null;
        const step = card ? card.offsetWidth + 16 : track.clientWidth * 0.85;

        track.scrollBy({
            left: direction * step,
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
        });
    }
}
