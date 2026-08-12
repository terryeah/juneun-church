/**
 * Supplies the pages of an album the grid has not rendered yet.
 *
 * Kept as an interface rather than a reference to InfiniteScroll so the
 * lightbox works on its own wherever a gallery is not paginated.
 */
export interface PhotoLoader {
    hasMore(): boolean;
    loadMore(): Promise<boolean>;
}

/**
 * Lightbox Component
 *
 * Displays gallery images in a fullscreen overlay with previous/next
 * and keyboard navigation.
 */
export class Lightbox {
    private loader: PhotoLoader | null;

    private container: HTMLElement;
    private overlay: HTMLElement | null = null;
    private stage: HTMLElement | null = null;
    private image: HTMLImageElement | null = null;
    private currentIndex: number = 0;
    private suppressOverlayClick: boolean = false;
    private pointers: Map<number, { x: number; y: number }> = new Map();
    private zoomScale: number = 1;
    private zoomX: number = 0;
    private zoomY: number = 0;
    private pinchStart: { distance: number; scale: number } | null = null;
    private gestureHadPinch: boolean = false;
    private lastTapAt: number = 0;
    private renderGeneration: number = 0;
    private opener: HTMLElement | null = null;
    private status: HTMLElement | null = null;
    private reducedMotion: boolean = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /**
     * Creates a new Lightbox instance.
     *
     * @param container - The gallery container element
     * @param loader - Supplies the rest of a paginated album, if any
     */
    constructor(container: HTMLElement, loader: PhotoLoader | null = null) {
        this.container = container;
        this.loader = loader;
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
     * How many photos the album holds, not how many are on the page.
     *
     * Counting the rendered links would announce "사진 1 / 24" in an
     * album of 806, which reads as though the album ended there. The
     * server states the real figure; without it the rendered count is
     * the whole album anyway.
     */
    private total(): number {
        const stated = Number(this.container.dataset.photoTotal);

        return Number.isFinite(stated) && stated > 0 ? stated : this.links().length;
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
        this.overlay.setAttribute('aria-label', '사진 크게 보기');
        this.overlay.tabIndex = -1;
        this.overlay.style.outline = 'none';

        this.status = document.createElement('span');
        this.status.setAttribute('role', 'status');
        this.status.className = 'sr-only';
        this.overlay.appendChild(this.status);
        this.overlay.style.opacity = '0';

        /**
         * An overlay at zero opacity is still there for a click, so
         * without this the first tap on the next thumbnail is eaten by
         * the photo that was just closed.
         */
        this.overlay.style.pointerEvents = 'none';
        this.overlay.style.transition = this.reducedMotion ? 'none' : 'opacity 280ms ease';

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
        image.style.cssText = 'position: absolute; inset: 0; margin: auto; max-width: 100%; max-height: 100%;';
        image.style.transition = this.reducedMotion
            ? 'none'
            : 'opacity 300ms ease, transform 360ms cubic-bezier(0.22, 1, 0.36, 1)';

        return image;
    }

    /**
     * Touch gestures: swipe left/right for the next and previous
     * photo, swipe down to close, pinch (or double-tap) to zoom and
     * one-finger pan while zoomed in.
     */
    private bindSwipe(): void {
        const overlay = this.overlay;
        if (!overlay) {
            return;
        }

        overlay.style.touchAction = 'none';

        let startX = 0;
        let startY = 0;
        let lastX = 0;
        let lastY = 0;

        overlay.addEventListener('pointerdown', (event: PointerEvent) => {
            this.pointers.set(event.pointerId, { x: event.clientX, y: event.clientY });

            if (this.pointers.size === 1) {
                startX = lastX = event.clientX;
                startY = lastY = event.clientY;
            }

            if (this.pointers.size === 2) {
                this.pinchStart = { distance: this.pointerDistance(), scale: this.zoomScale };
                this.gestureHadPinch = true;
                this.setImageTransition(false);
            }
        });

        overlay.addEventListener('pointermove', (event: PointerEvent) => {
            if (! this.pointers.has(event.pointerId)) {
                return;
            }

            this.pointers.set(event.pointerId, { x: event.clientX, y: event.clientY });

            if (this.pointers.size === 2 && this.pinchStart) {
                const ratio = this.pointerDistance() / this.pinchStart.distance;
                this.zoomScale = Math.min(4, Math.max(1, this.pinchStart.scale * ratio));
                this.applyZoom();

                return;
            }

            if (this.pointers.size === 1 && this.zoomScale > 1.05) {
                this.zoomX += event.clientX - lastX;
                this.zoomY += event.clientY - lastY;
                lastX = event.clientX;
                lastY = event.clientY;
                this.setImageTransition(false);
                this.applyZoom();
            }
        });

        const release = (event: PointerEvent): void => {
            this.pointers.delete(event.pointerId);

            if (this.pointers.size < 2) {
                this.pinchStart = null;
            }

            if (this.pointers.size > 0) {
                return;
            }

            const hadPinch = this.gestureHadPinch;
            this.gestureHadPinch = false;

            this.setImageTransition(true);

            if (hadPinch || this.zoomScale > 1.05) {
                if (this.zoomScale <= 1.05) {
                    this.resetZoom();
                }

                this.suppressOverlayClick = true;

                return;
            }

            const deltaX = event.clientX - startX;
            const deltaY = event.clientY - startY;

            if (Math.abs(deltaX) > 48 && Math.abs(deltaX) > Math.abs(deltaY)) {
                this.suppressOverlayClick = true;
                this.step(deltaX < 0 ? 1 : -1);

                return;
            }

            if (deltaY > 72 && Math.abs(deltaY) > Math.abs(deltaX)) {
                this.suppressOverlayClick = true;
                this.close();

                return;
            }

            /** Double-tap toggles between fit and 2.5x zoom. */
            if (Math.abs(deltaX) < 12 && Math.abs(deltaY) < 12 && event.pointerType === 'touch') {
                const now = Date.now();

                if (now - this.lastTapAt < 300) {
                    this.suppressOverlayClick = true;
                    this.zoomScale = this.zoomScale > 1.05 ? 1 : 2.5;

                    if (this.zoomScale === 1) {
                        this.resetZoom();
                    } else {
                        this.applyZoom();
                    }

                    this.lastTapAt = 0;

                    return;
                }

                this.lastTapAt = now;
            }
        };

        overlay.addEventListener('pointerup', release);
        overlay.addEventListener('pointercancel', release);
    }

    /**
     * The distance between the two active pinch pointers.
     */
    private pointerDistance(): number {
        const [a, b] = Array.from(this.pointers.values());

        return Math.hypot(a.x - b.x, a.y - b.y) || 1;
    }

    /**
     * Applies the current zoom scale and pan offset to the image.
     */
    private applyZoom(): void {
        if (this.image) {
            this.image.style.transform = `translate(${this.zoomX}px, ${this.zoomY}px) scale(${this.zoomScale})`;
        }
    }

    /**
     * Returns the image to its fitted, centred state.
     */
    private resetZoom(): void {
        this.zoomScale = 1;
        this.zoomX = 0;
        this.zoomY = 0;

        if (this.image) {
            this.setImageTransition(true);
            this.image.style.transform = 'translate(0px, 0px) scale(1)';
        }
    }

    /**
     * Toggles the image transition while gestures are in progress.
     */
    private setImageTransition(enabled: boolean): void {
        if (this.image) {
            this.image.style.transition = enabled && ! this.reducedMotion
                ? 'opacity 300ms ease, transform 360ms cubic-bezier(0.22, 1, 0.36, 1)'
                : 'none';
        }
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
            if (event.key === '+' || event.key === '=') {
                this.zoomScale = Math.min(4, this.zoomScale + 0.5);
                this.applyZoom();
            }
            if (event.key === '-') {
                this.zoomScale = Math.max(1, this.zoomScale - 0.5);
                this.zoomScale <= 1.05 ? this.resetZoom() : this.applyZoom();
            }
            if (event.key === '0') {
                this.resetZoom();
            }

            if (event.key === 'Tab') {
                const focusables = Array.from(
                    this.overlay?.querySelectorAll<HTMLElement>('button') ?? [],
                );

                if (focusables.length === 0) {
                    return;
                }

                const first = focusables[0];
                const last = focusables[focusables.length - 1];
                const active = document.activeElement as HTMLElement;

                if (event.shiftKey && (active === first || ! focusables.includes(active))) {
                    event.preventDefault();
                    last.focus();
                } else if (! event.shiftKey && active === last) {
                    event.preventDefault();
                    first.focus();
                }
            }
        });
    }

    /**
     * Opens the overlay at the given photo index.
     *
     * @param index - Position within the current link list
     */
    private open(index: number): void {
        this.opener = document.activeElement as HTMLElement;
        this.currentIndex = index;
        this.render(0);
        this.overlay?.classList.remove('hidden');
        this.overlay?.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        this.overlay?.focus({ preventScroll: true });

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
        this.zoomScale = 1;
        this.zoomX = 0;
        this.zoomY = 0;
        document.body.classList.remove('overflow-hidden');

        window.setTimeout(() => {
            this.overlay?.classList.add('hidden');

            if (this.overlay) {
                this.overlay.style.pointerEvents = '';
            }
            this.overlay?.classList.remove('flex');
            this.opener?.focus();
            this.opener = null;
        }, this.reducedMotion ? 0 : 280);
    }

    /**
     * Moves forwards or backwards through the photo list.
     *
     * Reaching the end of the grid fetches the next page before moving,
     * so the lightbox walks the whole album rather than the 24 photos
     * the page happened to be rendered with. Only once the album really
     * has run out does the last photo wrap round to the first.
     *
     * @param delta - +1 for next, -1 for previous
     */
    private async step(delta: number): Promise<void> {
        this.zoomScale = 1;
        this.zoomX = 0;
        this.zoomY = 0;

        if (delta > 0 && this.currentIndex === this.links().length - 1 && this.loader?.hasMore()) {
            if (this.status) {
                this.status.textContent = '사진을 더 불러오는 중';
            }

            await this.loader.loadMore();
        }

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
        const generation = ++this.renderGeneration;

        if (!link || !stage || !outgoing) {
            return;
        }

        if (this.status) {
            this.status.textContent = `사진 ${this.currentIndex + 1} / ${this.total()}`;
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

            if (generation !== this.renderGeneration) {
                return;
            }

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
