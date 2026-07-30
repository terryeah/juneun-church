/**
 * Lightbox Component
 *
 * Displays gallery images in a fullscreen overlay with previous/next
 * and keyboard navigation.
 */
export class Lightbox {
    private container: HTMLElement;
    private overlay: HTMLElement | null = null;
    private stage: HTMLElement | null = null;
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

        this.stage = document.createElement('div');
        this.stage.style.cssText = 'position: relative; width: 100%; height: 100%;';
        this.overlay.appendChild(this.stage);

        this.image = this.createImageLayer();
        this.stage.appendChild(this.image);

        const close = this.overlayButton('✕', 'absolute right-4 top-4', '닫기');
        close.addEventListener('click', () => this.close());

        const previous = this.overlayButton('‹', 'absolute left-4 top-1/2 -translate-y-1/2', '이전 사진');
        previous.addEventListener('click', () => this.step(-1));

        const next = this.overlayButton('›', 'absolute right-4 top-1/2 -translate-y-1/2', '다음 사진');
        next.addEventListener('click', () => this.step(1));

        this.overlay.addEventListener('click', (event: MouseEvent) => {
            if ((event.target === this.overlay || event.target === this.stage) && ! this.suppressOverlayClick) {
                this.close();
            }
            this.suppressOverlayClick = false;
        });

        this.bindSwipe();

        document.body.appendChild(this.overlay);
    }

    /**
     * A centred, size-constrained image layer for the crossfade stage.
     */
    private createImageLayer(): HTMLImageElement {
        const image = document.createElement('img');
        image.className = 'rounded-media';
        image.style.cssText = 'position: absolute; inset: 0; margin: auto; max-width: 100%; max-height: 100%; '
            + 'transition: opacity 300ms ease, transform 360ms cubic-bezier(0.22, 1, 0.36, 1);';

        return image;
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
     * Shows the photo at the current index. Photo changes crossfade:
     * the incoming image slides in over the outgoing one, which stays
     * visible underneath the whole time, so the screen never dips to
     * the dark backdrop between photos.
     *
     * @param direction - -1 from the left, +1 from the right, 0 still
     */
    private render(direction: number): void {
        const link = this.links()[this.currentIndex];
        const stage = this.stage;
        const outgoing = this.image;

        if (!link || !stage || !outgoing) {
            return;
        }

        if (direction === 0) {
            outgoing.onload = null;
            outgoing.style.transition = 'none';
            outgoing.style.opacity = '0';
            outgoing.style.transform = 'scale(0.965)';
            outgoing.src = link.href;
            outgoing.alt = link.querySelector('img')?.alt ?? '';

            const reveal = (): void => {
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        outgoing.style.transition = 'opacity 300ms ease, transform 360ms cubic-bezier(0.22, 1, 0.36, 1)';
                        outgoing.style.opacity = '1';
                        outgoing.style.transform = 'scale(1)';
                    });
                });
            };

            outgoing.complete ? reveal() : (outgoing.onload = reveal);

            return;
        }

        const incoming = this.createImageLayer();
        incoming.style.opacity = '0';
        incoming.style.transform = `translateX(${direction * 36}px)`;
        incoming.alt = link.querySelector('img')?.alt ?? '';
        this.image = incoming;

        const start = (): void => {
            incoming.onload = null;
            stage.appendChild(incoming);

            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    incoming.style.opacity = '1';
                    incoming.style.transform = 'translateX(0)';
                    outgoing.style.opacity = '0';
                    outgoing.style.transform = `translateX(${direction * -36}px)`;
                });
            });

            window.setTimeout(() => outgoing.remove(), 400);
        };

        incoming.onload = start;
        incoming.src = link.href;

        if (incoming.complete) {
            start();
        }
    }
}
