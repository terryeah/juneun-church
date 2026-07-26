/**
 * Photo Slider Component
 *
 * Automatically slides through the gallery preview band, advancing one
 * photo at a time and looping back to the start. Sliding pauses while
 * the pointer is over the band. Without JavaScript the first slides
 * remain visible as a static row.
 */
export class PhotoSlider {
    private container: HTMLElement;
    private track: HTMLElement | null;
    private currentIndex: number = 0;
    private paused: boolean = false;

    /**
     * Creates a new PhotoSlider instance.
     *
     * @param container - Element carrying the data-photo-slider attribute
     */
    constructor(container: HTMLElement) {
        this.container = container;
        this.track = container.querySelector<HTMLElement>('[data-slider-track]');
        this.bindEvents();
        this.start();
    }

    /**
     * The slide elements currently in the track.
     */
    private slides(): HTMLElement[] {
        return this.track ? (Array.from(this.track.children) as HTMLElement[]) : [];
    }

    /**
     * Pauses on hover so visitors can look at or click a photo.
     */
    private bindEvents(): void {
        this.container.addEventListener('pointerenter', () => {
            this.paused = true;
        });
        this.container.addEventListener('pointerleave', () => {
            this.paused = false;
        });
    }

    /**
     * Starts the automatic advance timer.
     */
    private start(): void {
        if (this.slides().length < 2) {
            return;
        }

        window.setInterval(() => {
            if (!this.paused) {
                this.advance();
            }
        }, 3500);
    }

    /**
     * Moves to the next photo, looping once the final one is reached.
     */
    private advance(): void {
        const slides = this.slides();
        if (slides.length === 0 || !this.track) {
            return;
        }

        const slideWidth = slides[0].offsetWidth;
        const visible = Math.max(1, Math.round(this.container.offsetWidth / slideWidth));
        const maxIndex = Math.max(0, slides.length - visible);

        this.currentIndex = this.currentIndex >= maxIndex ? 0 : this.currentIndex + 1;
        this.track.style.transform = `translateX(-${slides[this.currentIndex].offsetLeft}px)`;
    }
}
