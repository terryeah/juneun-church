/**
 * Photo Slider Component
 *
 * Automatically slides through the gallery preview band, advancing one
 * photo at a time and looping back to the start. Visitors can also
 * drag the band with a mouse or swipe it on touch screens; a dot
 * pagination below the band mirrors the current position and allows
 * jumping to any slide. Sliding pauses while the pointer is over the
 * band or a drag is in progress. Without JavaScript the first slides
 * remain visible as a static row.
 */
export class PhotoSlider {
    private container: HTMLElement;
    private track: HTMLElement | null;
    private dots: HTMLElement | null;
    private currentIndex: number = 0;
    private paused: boolean = false;
    private dragging: boolean = false;
    private dragMoved: boolean = false;
    private suppressClick: boolean = false;
    private dragStartX: number = 0;
    private dragStartOffset: number = 0;

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
        this.bindDrag();
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
     * Lets visitors drag the band with a mouse or finger. A drag that
     * actually moved suppresses the click so photo links only open on
     * a clean tap.
     */
    private bindDrag(): void {
        const track = this.track;
        if (!track) {
            return;
        }

        track.addEventListener('dragstart', (event: Event) => event.preventDefault());

        track.addEventListener('pointerdown', (event: PointerEvent) => {
            if (this.slides().length < 2) {
                return;
            }

            this.dragging = true;
            this.dragMoved = false;
            this.dragStartX = event.clientX;
            this.dragStartOffset = this.slides()[this.currentIndex]?.offsetLeft ?? 0;
            track.setPointerCapture(event.pointerId);
            track.style.transition = 'none';
        });

        track.addEventListener('pointermove', (event: PointerEvent) => {
            if (!this.dragging) {
                return;
            }

            const delta = event.clientX - this.dragStartX;

            if (Math.abs(delta) > 6) {
                this.dragMoved = true;
            }

            track.style.transform = `translateX(${-this.rubberBand(this.dragStartOffset - delta)}px)`;
        });

        const finish = (event: PointerEvent): void => {
            if (!this.dragging) {
                return;
            }

            this.dragging = false;
            track.style.transition = '';

            const delta = event.clientX - this.dragStartX;
            let target = this.nearestIndex(this.dragStartOffset - delta);

            if (target === this.currentIndex && Math.abs(delta) > 40) {
                target += delta < 0 ? 1 : -1;

                if (target > this.maxIndex()) {
                    target = 0;
                } else if (target < 0) {
                    target = this.maxIndex();
                }
            }

            this.goTo(target);

            if (this.dragMoved) {
                this.suppressClick = true;
            }
        };

        track.addEventListener('pointerup', finish);
        track.addEventListener('pointercancel', finish);

        this.container.addEventListener(
            'click',
            (event: Event) => {
                if (this.suppressClick) {
                    event.preventDefault();
                    event.stopPropagation();
                    this.suppressClick = false;
                }
            },
            { capture: true },
        );
    }

    /**
     * Softens the drag offset beyond the first and last positions.
     */
    private rubberBand(offset: number): number {
        const max = this.slides()[this.maxIndex()]?.offsetLeft ?? 0;

        if (offset < 0) {
            return offset / 3;
        }

        if (offset > max) {
            return max + (offset - max) / 3;
        }

        return offset;
    }

    /**
     * The slide index whose left edge is closest to the given offset.
     */
    private nearestIndex(offset: number): number {
        let nearest = 0;
        let smallest = Number.POSITIVE_INFINITY;

        this.slides().slice(0, this.maxIndex() + 1).forEach((slide, index) => {
            const distance = Math.abs(slide.offsetLeft - offset);

            if (distance < smallest) {
                smallest = distance;
                nearest = index;
            }
        });

        return nearest;
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
            if (!this.paused && !this.dragging) {
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
