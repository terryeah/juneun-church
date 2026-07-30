/**
 * Lightbox Component
 *
 * Displays gallery images in a fullscreen overlay with previous/next
 * and keyboard navigation.
 */
export class Lightbox {
    private container: HTMLElement;
    private overlay: HTMLElement | null = null;
    private image: HTMLImageElement | null = null;
    private currentIndex: number = 0;
    private suppressOverlayClick: boolean = false;

    /**
     * Creates a new Lightbox instance.
     *
     * @param container - The gallery container element
     */
    constructor(container: HTMLElement) {
        this.container = container;
        this.buildOverlay();
        this.bindEvents();
    }

    /**
     * The current list of lightbox links, re-read on demand so photos
     * appended by infinite scroll are included automatically.
     */
    private links(): HTMLAnchorElement[] {
        return Array.from(this.container.querySelectorAll<HTMLAnchorElement>('a[data-lightbox]'));
    }

    /**
     * Builds the fullscreen overlay once and appends it to the body.
     */
    private buildOverlay(): void {
        this.overlay = document.createElement('div');
        this.overlay.className =
            'fixed inset-0 z-50 hidden items-center justify-center bg-navy-900/95 p-4';
        this.overlay.setAttribute('role', 'dialog');
        this.overlay.setAttribute('aria-modal', 'true');
        this.overlay.style.opacity = '0';
        this.overlay.style.transition = 'opacity 280ms ease';

        this.image = document.createElement('img');
        this.image.className = 'max-h-full max-w-full rounded-media';
        this.image.style.transition = 'opacity 260ms ease, transform 320ms cubic-bezier(0.22, 1, 0.36, 1)';
        this.overlay.appendChild(this.image);

        const close = this.overlayButton('✕', 'absolute right-4 top-4', '닫기');
        close.addEventListener('click', () => this.close());

        const previous = this.overlayButton('‹', 'absolute left-4 top-1/2 -translate-y-1/2', '이전 사진');
        previous.addEventListener('click', () => this.step(-1));

        const next = this.overlayButton('›', 'absolute right-4 top-1/2 -translate-y-1/2', '다음 사진');
        next.addEventListener('click', () => this.step(1));

        this.overlay.addEventListener('click', (event: MouseEvent) => {
            if (event.target === this.overlay && ! this.suppressOverlayClick) {
                this.close();
            }
            this.suppressOverlayClick = false;
        });

        this.bindSwipe();

        document.body.appendChild(this.overlay);
    }

    /**
     * Lets touch users swipe left/right for the next and previous
     * photo, and swipe down to close, matching native gallery UX.
     */
    private bindSwipe(): void {
        const overlay = this.overlay;
        if (!overlay) {
            return;
        }

        overlay.style.touchAction = 'none';

        let startX = 0;
        let startY = 0;
        let tracking = false;

        overlay.addEventListener('pointerdown', (event: PointerEvent) => {
            tracking = true;
            startX = event.clientX;
            startY = event.clientY;
        });

        overlay.addEventListener('pointerup', (event: PointerEvent) => {
            if (!tracking) {
                return;
            }

            tracking = false;
            const deltaX = event.clientX - startX;
            const deltaY = event.clientY - startY;

            if (Math.abs(deltaX) > 48 && Math.abs(deltaX) > Math.abs(deltaY)) {
                this.suppressOverlayClick = true;
                this.step(deltaX < 0 ? 1 : -1);
            } else if (deltaY > 72 && Math.abs(deltaY) > Math.abs(deltaX)) {
                this.suppressOverlayClick = true;
                this.close();
            }
        });

        overlay.addEventListener('pointercancel', () => {
            tracking = false;
        });
    }

    /**
     * Creates a styled overlay control button.
     *
     * @param label - The visible glyph
     * @param position - Positioning utility classes
     * @param title - Accessible label in Korean
     */
    private overlayButton(label: string, position: string, title: string): HTMLButtonElement {
        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = label;
        button.setAttribute('aria-label', title);
        button.className = `${position} rounded-nav px-3 py-2 text-2xl text-cream hover:bg-navy-700`;
        this.overlay?.appendChild(button);

        return button;
    }

    /**
     * Binds gallery click and keyboard handlers.
     */
    private bindEvents(): void {
        this.container.addEventListener('click', (event: MouseEvent) => {
            const link = (event.target as HTMLElement).closest<HTMLAnchorElement>('a[data-lightbox]');
            if (!link) {
                return;
            }

            event.preventDefault();
            this.open(this.links().indexOf(link));
        });

        document.addEventListener('keydown', (event: KeyboardEvent) => {
            if (this.overlay?.classList.contains('hidden')) {
                return;
            }

            if (event.key === 'Escape') {
                this.close();
            }
            if (event.key === 'ArrowLeft') {
                this.step(-1);
            }
            if (event.key === 'ArrowRight') {
                this.step(1);
            }
        });
    }

    /**
     * Opens the overlay at the given photo index.
     *
     * @param index - Position within the current link list
     */
    private open(index: number): void {
        this.currentIndex = index;
        this.render(0);
        this.overlay?.classList.remove('hidden');
        this.overlay?.classList.add('flex');
        document.body.classList.add('overflow-hidden');

        requestAnimationFrame(() => {
            if (this.overlay) {
                this.overlay.style.opacity = '1';
            }
        });
    }

    /**
     * Closes the overlay with a fade and restores scrolling.
     */
    private close(): void {
        if (!this.overlay) {
            return;
        }

        this.overlay.style.opacity = '0';
        document.body.classList.remove('overflow-hidden');

        window.setTimeout(() => {
            this.overlay?.classList.add('hidden');
            this.overlay?.classList.remove('flex');
        }, 280);
    }

    /**
     * Moves forwards or backwards through the photo list.
     *
     * @param delta - +1 for next, -1 for previous
     */
    private step(delta: number): void {
        const links = this.links();
        this.currentIndex = (this.currentIndex + delta + links.length) % links.length;
        this.render(delta);
    }

    /**
     * Shows the photo at the current index, easing the outgoing image
     * away and sliding the incoming one in from the travel direction.
     *
     * @param direction - -1 from the left, +1 from the right, 0 still
     */
    private render(direction: number): void {
        const link = this.links()[this.currentIndex];
        const image = this.image;

        if (!link || !image) {
            return;
        }

        image.style.opacity = '0';
        image.style.transform = direction === 0 ? 'scale(0.965)' : `translateX(${direction * -28}px) scale(0.985)`;

        const swap = (): void => {
            const reveal = (): void => {
                image.onload = null;
                image.style.transition = 'none';
                image.style.transform = direction === 0 ? 'scale(0.965)' : `translateX(${direction * 28}px) scale(0.985)`;

                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        image.style.transition = 'opacity 260ms ease, transform 320ms cubic-bezier(0.22, 1, 0.36, 1)';
                        image.style.opacity = '1';
                        image.style.transform = 'translateX(0) scale(1)';
                    });
                });
            };

            image.onload = reveal;
            image.src = link.href;
            image.alt = link.querySelector('img')?.alt ?? '';

            if (image.complete) {
                reveal();
            }
        };

        window.setTimeout(swap, direction === 0 ? 0 : 150);
    }
}
