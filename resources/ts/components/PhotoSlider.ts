/**
 * Photo Carousel Component
 *
 * Apple-style horizontal carousel: native scroll-snap handles touch
 * swipes and trackpads, and the circular arrow buttons page through
 * one viewport at a time. The slide set is cloned once so the band
 * loops seamlessly - crossing into the cloned set teleports the
 * scroll position back by exactly one set width, which is invisible
 * because both sets render identically. Without JavaScript the band
 * is a plain scrollable row of photo links.
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
            clone.setAttribute('aria-hidden', 'true');
            clone.setAttribute('tabindex', '-1');
            clone.querySelectorAll('a').forEach((link) => link.setAttribute('tabindex', '-1'));
            this.track!.appendChild(clone);
        });
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
     * Pages by roughly one visible viewport, wrapping at both ends.
     *
     * @param direction - -1 for backwards, +1 for forwards
     */
    private page(direction: number): void {
        const track = this.track!;

        /** Going backwards from the start borrows the cloned set first. */
        if (direction < 0 && track.scrollLeft <= 2 && this.setWidth > 0) {
            this.jump(track.scrollLeft + this.setWidth);
        }

        track.scrollBy({
            left: direction * track.clientWidth * 0.85,
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
        });
    }
}
