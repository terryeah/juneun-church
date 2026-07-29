/**
 * Photo Slider Component
 *
 * Automatically slides through the gallery preview band, advancing one
 * photo at a time and looping back to the start. Sliding pauses while
 * the pointer is over the band, and a dot pagination below the band
 * mirrors the current position and allows jumping to any slide.
 * Without JavaScript the first slides remain visible as a static row.
 */
export class PhotoSlider {
    private container: HTMLElement;
    private track: HTMLElement | null;
    private dots: HTMLElement | null;
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
        this.dots = container.closest('section')?.querySelector<HTMLElement>('[data-slider-dots]') ?? null;
        this.bindEvents();
        this.renderDots();
        this.start();
    }

    /**
     * The slide elements currently in the track.
     */
    private slides(): HTMLElement[] {
        return this.track ? (Array.from(this.track.children) as HTMLElement[]) : [];
    }

    /**
     * The highest reachable slide index for the current viewport.
     */
    private maxIndex(): number {
        const slides = this.slides();
        if (slides.length === 0) {
            return 0;
        }

        const slideWidth = slides[0].offsetWidth;
        const visible = Math.max(1, Math.round(this.container.offsetWidth / slideWidth));

        return Math.max(0, slides.length - visible);
    }

    /**
     * Pauses on hover so visitors can look at or click a photo, and
     * rebuilds the dots whenever the viewport size changes.
     */
    private bindEvents(): void {
        this.container.addEventListener('pointerenter', () => {
            this.paused = true;
        });
        this.container.addEventListener('pointerleave', () => {
            this.paused = false;
        });
        window.addEventListener('resize', () => {
            this.renderDots();
            this.goTo(Math.min(this.currentIndex, this.maxIndex()));
        });
    }

    /**
     * Builds one pagination dot per reachable slide position.
     */
    private renderDots(): void {
        if (!this.dots) {
            return;
        }

        this.dots.innerHTML = '';

        for (let index = 0; index <= this.maxIndex(); index++) {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'h-1.5 w-1.5 rounded-full bg-cream/30 transition-colors duration-300 hover:bg-cream/60';
            dot.setAttribute('aria-label', `${index + 1}번째 사진으로 이동`);
            dot.addEventListener('click', () => this.goTo(index));
            this.dots?.appendChild(dot);
        }

        this.updateDots();
    }

    /**
     * Highlights the dot matching the current slide position.
     */
    private updateDots(): void {
        if (!this.dots) {
            return;
        }

        Array.from(this.dots.children).forEach((dot, index) => {
            dot.classList.toggle('bg-cream', index === this.currentIndex);
            dot.classList.toggle('bg-cream/30', index !== this.currentIndex);
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
     * Moves the track to the given slide position.
     *
     * @param index - The slide index to align to the left edge
     */
    private goTo(index: number): void {
        const slides = this.slides();
        if (slides.length === 0 || !this.track) {
            return;
        }

        this.currentIndex = Math.max(0, Math.min(index, this.maxIndex()));
        this.track.style.transform = `translateX(-${slides[this.currentIndex].offsetLeft}px)`;
        this.updateDots();
    }

    /**
     * Moves to the next photo, looping once the final one is reached.
     */
    private advance(): void {
        this.goTo(this.currentIndex >= this.maxIndex() ? 0 : this.currentIndex + 1);
    }
}
