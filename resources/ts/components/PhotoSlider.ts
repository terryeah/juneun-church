/**
 * Photo Carousel Component
 *
 * Apple-style horizontal carousel: native scroll-snap handles touch
 * swipes and trackpads, while the circular arrow buttons page through
 * one viewport at a time. The arrows grey out at either end. Without
 * JavaScript the band is still a scrollable row of photo links.
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

        this.prev?.addEventListener('click', () => this.page(-1));
        this.next?.addEventListener('click', () => this.page(1));
        this.track.addEventListener('scroll', () => this.updateButtons(), { passive: true });
        window.addEventListener('resize', () => this.updateButtons());
        this.updateButtons();
    }

    /**
     * Scrolls the band by roughly one visible viewport of cards.
     *
     * @param direction - -1 for backwards, +1 for forwards
     */
    private page(direction: number): void {
        this.track?.scrollBy({
            left: direction * this.track.clientWidth * 0.85,
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
        });
    }

    /**
     * Greys out an arrow when its end of the band is reached.
     */
    private updateButtons(): void {
        const track = this.track;
        if (!track) {
            return;
        }

        if (this.prev) {
            this.prev.disabled = track.scrollLeft <= 2;
        }

        if (this.next) {
            this.next.disabled = track.scrollLeft >= track.scrollWidth - track.clientWidth - 2;
        }
    }
}
