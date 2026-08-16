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
 * The three marks the overlay controls carry.
 */
type OverlayIcon = 'close' | 'previous' | 'next';

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
     * The thumbnail the grid is already showing for a photo.
     *
     * It is on screen, so the browser has it: painting it costs nothing
     * and gives the reader the picture at once while the full-size file
     * is still on its way.
     */
    private thumbnailFor(link: HTMLAnchorElement): string | null {
        return link.querySelector('img')?.currentSrc || link.querySelector('img')?.src || null;
    }

    /**
     * Hold the layer at the size the full-size photo will occupy, from
     * the dimensions the page states, so the picture does not change
     * size when the original replaces the thumbnail it opened on.
     *
     * The box is fitted to the photo rather than to the stage, so the
     * rounded corners land on the picture instead of on empty space
     * beside it.
     *
     * Without the dimensions it falls back to what it did before, which
     * is correct but sizes itself from whichever file has loaded - and
     * so still grows when the original lands.
     */
    private sizeLayer(layer: HTMLImageElement, link: HTMLAnchorElement): void {
        const width = Number(link.dataset.width);
        const height = Number(link.dataset.height);
        const stage = this.stage;

        /** A stage with no size cannot fit anything; leave the layer alone. */
        if (! width || ! height || ! stage || stage.clientWidth === 0 || stage.clientHeight === 0) {
            layer.style.width = '';
            layer.style.height = '';
            layer.style.maxWidth = '100%';
            layer.style.maxHeight = '100%';

            return;
        }

        /**
         * Never larger than the original: a third of this gallery is
         * under 1000px wide and the smallest is 297px, so filling the
         * stage regardless would blow those up into mush.
         */
        const scale = Math.min(stage.clientWidth / width, stage.clientHeight / height, 1);

        layer.style.width = `${Math.round(width * scale)}px`;
        layer.style.height = `${Math.round(height * scale)}px`;
        layer.style.maxWidth = '100%';
        layer.style.maxHeight = '100%';
    }

    /**
     * Re-fit the photo on screen when the window changes shape.
     */
    private resizeCurrentLayer(): void {
        const link = this.links()[this.currentIndex];

        if (link && this.image) {
            this.sizeLayer(this.image, link);
        }
    }

    /**
     * Warm the photos either side of this one, so the next swipe paints
     * from memory instead of opening a connection and unpacking a
     * megapixel while the animation is trying to run.
     *
     * Downloading is only half of it: an image that is in the cache but
     * not yet decoded still costs that decode at the moment it is shown.
     * decode() does the work now, off the main thread, while nobody is
     * waiting.
     */
    private preloadNeighbours(): void {
        const links = this.links();

        for (const index of [this.currentIndex + 1, this.currentIndex - 1]) {
            const href = links[index]?.href;

            if (! href) {
                continue;
            }

            const image = new Image();
            image.decoding = 'async';
            image.src = href;
            image.decode?.().catch(() => undefined);
        }
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

        const close = this.overlayButton('close', 'absolute right-4 top-4', '닫기');
        close.addEventListener('click', () => this.close());

        const previous = this.overlayButton('previous', 'absolute left-6 top-1/2 -translate-y-1/2', '이전 사진');
        previous.addEventListener('click', () => this.step(-1));

        const next = this.overlayButton('next', 'absolute right-6 top-1/2 -translate-y-1/2', '다음 사진');
        next.addEventListener('click', () => this.step(1));

        this.overlay.addEventListener('click', (event: MouseEvent) => {
            if ((event.target === this.overlay || event.target === this.stage) && ! this.suppressOverlayClick) {
                this.close();
            }
            this.suppressOverlayClick = false;
        });

        this.bindSwipe();

        /** A rotated phone or a dragged window changes what fits. */
        window.addEventListener('resize', () => this.resizeCurrentLayer());

        document.body.appendChild(this.overlay);
    }

    /**
     * A centred, size-constrained image layer for the crossfade stage.
     */
    private createImageLayer(): HTMLImageElement {
        const image = document.createElement('img');
        image.className = 'rounded-media';
        /** The box is fitted to the photo, so this rounds the picture itself. */
        /**
         * Never decode on the main thread. A 1280x853 photo is a
         * megapixel to unpack, and doing it synchronously is a freeze
         * landing exactly as the swipe animation should be running.
         */
        image.decoding = 'async';
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

                return;
            }

            /**
             * Not zoomed: the photo follows the finger. Without this the
             * gesture had no answer until it ended, so a swipe that was
             * being read looked exactly like one that was being ignored.
             */
            if (this.pointers.size === 1 && this.image) {
                const deltaX = event.clientX - startX;
                const deltaY = event.clientY - startY;

                if (Math.abs(deltaY) > Math.abs(deltaX)) {
                    return;
                }

                this.setImageTransition(false);
                this.image.style.transform = `translateX(${deltaX}px)`;
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

            /** A drag that did not go far enough springs back. */
            if (this.image && this.image.style.transform.startsWith('translateX')) {
                this.image.style.transform = 'translateX(0)';
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
     * @param icon - Which mark the button carries
     * @param position - Positioning utility classes
     * @param title - Accessible label in Korean
     */
    private overlayButton(icon: OverlayIcon, position: string, title: string): HTMLButtonElement {
        const button = document.createElement('button');
        button.type = 'button';
        button.innerHTML = Lightbox.icon(icon);
        button.setAttribute('aria-label', title);
        button.className = position;

        /**
         * A dark disc under the glyph. The controls were bare cream
         * marks on whatever the photo happened to be, so on a bright one
         * - a poster, a snow scene - they disappeared into it. The disc
         * carries its own contrast whatever it sits on.
         *
         * A plain translucent colour, deliberately, with no backdrop
         * blur. A blur samples what is painted behind it, and WebKit
         * does not re-sample when that is a transformed layer - so once
         * a photo was pinched the discs kept showing the blur of where
         * it had been when it loaded, frozen, while the photo moved
         * underneath. Alpha compositing has nothing to freeze.
         *
         * Written inline rather than as utilities so the colour does not
         * depend on a Tailwind class surviving the scan of a file that
         * builds its markup in TypeScript, and as a plain rgba() rather
         * than color-mix() so it does not depend on a colour function
         * either - navy-900 is #0d1730.
         */
        button.style.cssText = 'display: flex; align-items: center; justify-content: center;'
            + ' width: 2.5rem; height: 2.5rem; border-radius: 9999px; line-height: 1;'
            + ' color: var(--color-cream);'
            + ' background-color: rgba(13, 23, 48, 0.72);'
            + ' transition: background-color 150ms ease;';

        button.addEventListener('pointerenter', () => {
            button.style.backgroundColor = 'rgba(13, 23, 48, 0.88)';
        });

        button.addEventListener('pointerleave', () => {
            button.style.backgroundColor = 'rgba(13, 23, 48, 0.72)';
        });

        this.overlay?.appendChild(button);

        return button;
    }

    /**
     * The mark a control carries, drawn rather than typed.
     *
     * These were text glyphs - ✕ and the chevrons - and a font gives
     * each of those whatever size and weight it likes: at one type size
     * the ✕ came out 19.5px tall against the chevrons' 11.5px, and no
     * type size fixes the stroke weight underneath. Drawn from one
     * viewBox at one stroke width, the three marks are the same by
     * construction rather than by a number somebody measured once.
     *
     * 22px in a 2.5rem disc, drawn at the size it was settled on: the
     * disc came in around it rather than the other way about.
     */
    private static icon(name: OverlayIcon): string {
        /**
         * The chevrons are drawn taller than the ✕ on purpose. Given
         * the same height they measure the same and still look smaller,
         * because a chevron is one thin bend where a ✕ fills a square
         * with two crossing strokes - so the eye is comparing 5px of
         * mark against 12px. Heroicons' own pair solves this by running
         * the chevron 15 units against the ✕'s 12, and both were held
         * against each other here before these numbers were kept.
         */
        const paths: Record<OverlayIcon, string> = {
            close: 'M6 6 18 18M18 6 6 18',
            previous: 'M15.75 19.5 8.25 12l7.5-7.5',
            next: 'M8.25 4.5l7.5 7.5-7.5 7.5',
        };

        return '<svg viewBox="0 0 24 24" width="22" height="22" fill="none"'
            + ' stroke="currentColor" stroke-width="2" stroke-linecap="round"'
            + ` stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="${paths[name]}"/></svg>`;
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

        /**
         * Shown before the photo is laid out, not after. Fitting the
         * layer to the stage needs the stage to have a size, and a
         * hidden one measures zero - so the first photo of every album
         * was laid out 0x0 and never appeared, while every photo after
         * it, measured against an overlay already on screen, was fine.
         */
        this.overlay?.classList.remove('hidden');
        this.overlay?.classList.add('flex');
        this.render(0);

        /**
         * The overlay is built click-through so that a faded-out one
         * does not eat the next tap on the grid behind it. Opening has
         * to hand that back, and did not - so the lightbox spent its
         * whole life transparent to touch: the backdrop tap fell through
         * to the header or the grid instead of closing it, a swipe never
         * reached the pointer handlers at all, and the previous and next
         * buttons passed the tap down to whatever thumbnail sat behind
         * them, which opened that photo and looked like one step.
         *
         * It survived every test because a dispatched click ignores
         * pointer-events; only a real finger notices.
         */
        if (this.overlay) {
            this.overlay.style.pointerEvents = 'auto';
        }

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

        if (!link || !stage) {
            return;
        }

        /** Only the opening render reuses the existing layer. */
        if (direction === 0 && ! outgoing) {
            return;
        }

        if (this.status) {
            this.status.textContent = `사진 ${this.currentIndex + 1} / ${this.total()}`;
        }

        if (direction === 0 && outgoing) {
            outgoing.onload = null;
            this.sizeLayer(outgoing, link);
            outgoing.style.transition = 'none';
            outgoing.style.opacity = '0';
            outgoing.style.transform = 'scale(0.965)';
            outgoing.src = this.thumbnailFor(link) ?? link.href;
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

            window.setTimeout(() => {
                this.upgradeToFullSize(outgoing, link.href, generation);
                this.preloadNeighbours();
            }, 400);

            return;
        }

        const incoming = this.createImageLayer();
        incoming.style.opacity = '0';
        incoming.style.transform = `translateX(${direction * 36}px)`;
        incoming.alt = link.querySelector('img')?.alt ?? '';
        this.sizeLayer(incoming, link);

        const thumbnail = this.thumbnailFor(link);

        const start = (): void => {
            incoming.onload = null;

            if (generation !== this.renderGeneration) {
                return;
            }

            /**
             * What leaves is whatever is on the stage right now, not
             * whatever this.image points at.
             *
             * A move that is superseded before its photo loads never
             * appends its layer, and the old code had already pointed
             * this.image at it. The next move then faded that detached
             * layer instead of the one the reader could see, so the
             * photo on screen stayed put and never got removed - the
             * counter climbed, nothing changed, and a layer piled up on
             * every attempt. Tapping next twice before a photo has
             * loaded is all it took, which on mobile data is most of
             * the time.
             */
            const leaving = Array.from(stage.querySelectorAll('img'));

            stage.appendChild(incoming);
            this.image = incoming;

            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    incoming.style.opacity = '1';
                    incoming.style.transform = 'translateX(0)';

                    for (const layer of leaving) {
                        layer.style.opacity = '0';
                        layer.style.transform = `translateX(${direction * -36}px)`;
                    }
                });
            });

            /**
             * Both of these wait for the move to finish. Reading
             * clientWidth forces a layout, and starting a fetch mid
             * animation competes with it - neither belongs on the frames
             * the reader is watching.
             */
            window.setTimeout(() => {
                for (const layer of leaving) {
                    layer.remove();
                }

                this.upgradeToFullSize(incoming, link.href, generation);
                this.preloadNeighbours();
            }, 400);
        };

        /**
         * The move starts on the thumbnail, which the grid behind the
         * overlay has already loaded, and the full-size file replaces it
         * once it arrives.
         *
         * Waiting for the full file before starting is what made a swipe
         * feel broken: the finger left the screen, nothing moved for as
         * long as 223 KB takes on a phone, and the reader swiped again
         * thinking the first one had missed.
         */
        incoming.onload = start;
        incoming.src = thumbnail ?? link.href;

        if (incoming.complete) {
            start();
        }
    }

    /**
     * Swap a layer showing the thumbnail for the full-size photo, once
     * that has loaded and if the reader has not moved on since.
     */
    private upgradeToFullSize(layer: HTMLImageElement, href: string, generation: number): void {
        if (layer.src === href) {
            return;
        }

        /**
         * Skip it when the thumbnail already has more pixels than the
         * screen can show. On a phone the 800px thumbnail covers the
         * frame, so fetching the 1280px original bought nothing anybody
         * could see and cost a download and a decode on every swipe.
         */
        const ratio = layer.naturalHeight > 0 ? layer.naturalWidth / layer.naturalHeight : 0;
        const painted = ratio > 0
            ? Math.min(layer.clientWidth, layer.clientHeight * ratio)
            : layer.clientWidth;
        const needed = painted * (window.devicePixelRatio || 1);

        if (needed > 0 && layer.naturalWidth >= needed) {
            return;
        }

        const full = new Image();
        full.decoding = 'async';

        /**
         * The swap waits for the decode, not just the download. Setting
         * src on a downloaded-but-undecoded image makes the browser
         * unpack it there and then, which is a stall on the frame the
         * reader is looking at.
         */
        const swap = (): void => {
            if (generation === this.renderGeneration && layer.isConnected) {
                layer.src = href;
            }
        };

        full.src = href;

        if (full.decode) {
            full.decode().then(swap).catch(() => undefined);

            return;
        }

        full.onload = swap;
    }
}
