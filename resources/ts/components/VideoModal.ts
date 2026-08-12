/**
 * Video Modal Component
 *
 * Plays an album's videos in the same fullscreen overlay the lightbox
 * uses for photographs, so opening a video album feels like opening a
 * photo album rather than leaving the site.
 *
 * The YouTube frame is written into the page when a video is opened and
 * removed when it is closed. Nothing of YouTube's is loaded while the
 * album page is merely being looked at, and closing the modal stops the
 * sound rather than leaving it playing behind the page.
 */
export class VideoModal {
    private container: HTMLElement;
    private overlay: HTMLElement | null = null;
    private stage: HTMLElement | null = null;
    private frame: HTMLIFrameElement | null = null;
    private heading: HTMLElement | null = null;
    private opener: HTMLElement | null = null;
    private reducedMotion: boolean = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    private closing: number | null = null;

    /**
     * Creates a new VideoModal instance.
     *
     * @param container - The element holding the video cards
     */
    constructor(container: HTMLElement) {
        this.container = container;
        this.buildOverlay();
        this.bindEvents();
    }

    /**
     * Builds the overlay once and appends it to the body.
     *
     * The chrome matches the lightbox deliberately: same backdrop, same
     * close button in the same corner, so the two do not feel like two
     * different sites.
     */
    private buildOverlay(): void {
        this.overlay = document.createElement('div');
        this.overlay.className =
            'fixed inset-0 z-50 hidden items-center justify-center bg-navy-900/95 p-4';
        this.overlay.setAttribute('role', 'dialog');
        this.overlay.setAttribute('aria-modal', 'true');
        this.overlay.setAttribute('aria-label', '동영상 보기');
        this.overlay.tabIndex = -1;
        this.overlay.style.outline = 'none';
        this.overlay.style.opacity = '0';
        this.overlay.style.transition = this.reducedMotion ? 'none' : 'opacity 280ms ease';

        this.stage = document.createElement('div');
        this.stage.className = 'w-full max-w-5xl';
        this.stage.style.cssText = 'position: relative;';
        this.overlay.appendChild(this.stage);

        this.heading = document.createElement('p');
        this.heading.className = 'mb-3 pr-24 font-kr text-body text-cream';
        this.stage.appendChild(this.heading);

        const close = this.overlayButton('✕', 'absolute right-0 -top-12', '닫기');
        close.addEventListener('click', () => this.close());

        const expand = this.overlayButton('⛶', 'absolute right-12 -top-12', '전체 화면');
        expand.addEventListener('click', () => void this.toggleFullscreen());

        /**
         * The backdrop closes, the player does not: a click landing on
         * the video is meant for the video.
         */
        this.overlay.addEventListener('click', (event: MouseEvent) => {
            if (event.target === this.overlay) {
                this.close();
            }
        });

        document.body.appendChild(this.overlay);
    }

    /**
     * Builds one of the overlay's chrome buttons.
     *
     * @param label - The glyph shown
     * @param position - Positioning classes
     * @param title - The accessible name
     */
    private overlayButton(label: string, position: string, title: string): HTMLButtonElement {
        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = label;
        button.setAttribute('aria-label', title);
        button.className = `${position} rounded-nav px-3 py-2 text-2xl text-cream hover:bg-navy-700`;

        /**
         * Appended to the stage, not the overlay, because the stage is
         * what goes fullscreen: a button on the overlay is outside that
         * subtree and vanishes the moment fullscreen starts, leaving
         * Escape as the only way back out.
         */
        this.stage?.appendChild(button);

        return button;
    }

    /**
     * Binds the card and keyboard handlers.
     */
    private bindEvents(): void {
        this.container.addEventListener('click', (event: MouseEvent) => {
            const card = (event.target as HTMLElement).closest<HTMLElement>('[data-video]');
            if (!card) {
                return;
            }

            event.preventDefault();
            this.open(card);
        });

        document.addEventListener('keydown', (event: KeyboardEvent) => {
            if (this.overlay?.classList.contains('hidden')) {
                return;
            }

            if (event.key === 'Escape' && !document.fullscreenElement) {
                this.close();

                return;
            }

            /**
             * Keep Tab inside the dialog. Without this it walks off the
             * last button into the page behind, which aria-modal has
             * already told a screen reader is not there.
             */
            if (event.key === 'Tab') {
                this.trapTab(event);
            }
        });
    }

    /**
     * Cycles focus between the dialog's own controls.
     *
     * @param event - The Tab keypress
     */
    private trapTab(event: KeyboardEvent): void {
        const focusable = Array.from(
            this.stage?.querySelectorAll<HTMLElement>('button, iframe') ?? [],
        );

        if (focusable.length === 0) {
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        const active = document.activeElement;

        if (event.shiftKey && (active === first || active === this.overlay)) {
            event.preventDefault();
            last.focus();

            return;
        }

        if (!event.shiftKey && active === last) {
            event.preventDefault();
            first.focus();
        }
    }

    /**
     * Opens the modal on a card's video.
     *
     * @param card - The card that was pressed
     */
    private open(card: HTMLElement): void {
        const embed = card.dataset.videoEmbed;
        if (!embed || !this.overlay || !this.stage) {
            return;
        }

        /**
         * Cancel a close still fading out. Its timer would otherwise
         * hide the overlay a moment after this one opened, leaving a
         * video playing inside an invisible modal with the page locked
         * behind it and Escape doing nothing.
         */
        if (this.closing !== null) {
            window.clearTimeout(this.closing);
            this.closing = null;
        }

        this.overlay.style.pointerEvents = '';
        this.opener = card;

        if (this.heading) {
            this.heading.textContent = card.dataset.videoTitle ?? '';
        }

        /** Never two players. A leftover frame would keep playing under the new one. */
        this.frame?.remove();

        this.frame = document.createElement('iframe');
        this.frame.src = embed;
        this.frame.title = card.dataset.videoTitle ?? '동영상';
        this.frame.className = 'w-full rounded-media';
        this.frame.style.cssText = 'aspect-ratio: 16 / 9; border: 0; display: block;';
        /** What a church video needs and nothing else - no motion sensors, no clipboard. */
        this.frame.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
        this.frame.allowFullscreen = true;
        this.frame.referrerPolicy = 'strict-origin-when-cross-origin';
        this.stage.appendChild(this.frame);

        this.overlay.classList.remove('hidden');
        this.overlay.classList.add('flex');
        document.body.style.overflow = 'hidden';

        requestAnimationFrame(() => {
            if (this.overlay) {
                this.overlay.style.opacity = '1';
            }
        });

        this.overlay.focus();
    }

    /**
     * Closes the modal and tears the player down.
     *
     * Removing the frame is what stops the sound; hiding the overlay
     * would leave it playing behind the page.
     */
    private close(): void {
        if (!this.overlay) {
            return;
        }

        if (document.fullscreenElement) {
            void document.exitFullscreen().catch(() => undefined);
        }

        this.overlay.style.opacity = '0';

        /**
         * The player, the scroll lock and the focus go back at once.
         * Only the fade waits: an overlay at zero opacity is still
         * there for a click, so leaving it until the animation ended
         * swallowed the next tap for 280ms and kept the page locked.
         */
        this.overlay.style.pointerEvents = 'none';
        this.frame?.remove();
        this.frame = null;
        document.body.style.overflow = '';
        this.opener?.focus();
        this.opener = null;

        const finish = (): void => {
            this.overlay?.classList.add('hidden');
            this.overlay?.classList.remove('flex');

            if (this.overlay) {
                this.overlay.style.pointerEvents = '';
            }
        };

        if (this.reducedMotion) {
            finish();

            return;
        }

        this.closing = window.setTimeout(() => {
            this.closing = null;
            finish();
        }, 280);
    }

    /**
     * Puts the player on the whole screen, or takes it back off.
     *
     * The stage rather than the frame goes fullscreen, so the title and
     * the close button stay with the video.
     */
    private async toggleFullscreen(): Promise<void> {
        if (!this.stage) {
            return;
        }

        try {
            if (document.fullscreenElement) {
                await document.exitFullscreen();

                return;
            }

            await this.stage.requestFullscreen();
        } catch {
            /**
             * Some browsers refuse fullscreen outside a gesture they
             * recognise, and iOS Safari has no element fullscreen at
             * all. The video keeps playing in the modal either way, and
             * YouTube's own control still offers its own fullscreen.
             */
        }
    }
}
