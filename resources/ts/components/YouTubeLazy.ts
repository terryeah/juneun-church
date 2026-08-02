/**
 * YouTube Lazy Loader Component
 *
 * Desktop (fine pointer): renders a poster with a play glyph and swaps
 * in an autoplaying YouTube iframe on click - autoplay inside a click
 * gesture works there, so playback starts on the first click.
 *
 * Touch devices: iOS and Android refuse autoplay in a freshly injected
 * iframe, which used to force a second tap on YouTube's own button. So
 * on coarse pointers the iframe mounts as soon as the card nears the
 * viewport, with the Apple-style poster overlay kept on top at
 * pointer-events none. The visitor's single tap lands straight on the
 * YouTube play button beneath the overlay, and the overlay dissolves
 * the moment the player reports it is playing.
 *
 * Without JavaScript the element remains a plain link to YouTube. If
 * the player reports an error - an embed-restricted upload, or a
 * browser that blocks the embed's storage - the poster is restored and
 * the click falls through to YouTube in a new tab.
 */
export class YouTubeLazy {
    private container: HTMLElement;
    private videoId: string;
    private player: HTMLElement | null = null;
    private overlay: HTMLElement | null = null;
    private failed: boolean = false;

    /**
     * Creates a new YouTubeLazy instance.
     *
     * @param container - Element carrying the data-youtube-lazy attribute
     */
    constructor(container: HTMLElement) {
        this.container = container;
        this.videoId = container.dataset.youtubeLazy ?? '';

        if (this.videoId === '') {
            return;
        }

        if (window.matchMedia('(pointer: coarse)').matches) {
            this.mountWhenNear();
        } else {
            this.bindClick();
        }
    }

    /**
     * Desktop path: intercept the click and load an autoplaying embed.
     */
    private bindClick(): void {
        this.container.addEventListener('click', (event: Event) => {
            if (this.failed) {
                return;
            }

            event.preventDefault();
            this.loadIframe(true);
        });
    }

    /**
     * Touch path: mount the real player once the card nears view.
     */
    private mountWhenNear(): void {
        const observer = new IntersectionObserver(
            (entries) => {
                if (entries.some((entry) => entry.isIntersecting)) {
                    observer.disconnect();
                    this.loadIframe(false);
                }
            },
            { rootMargin: '200px' },
        );

        observer.observe(this.container);
    }

    /**
     * Builds the YouTube embed and subscribes to player API events so
     * failures restore the poster and playback dissolves the overlay.
     *
     * @param autoplay - Whether the embed should start immediately
     */
    private loadIframe(autoplay: boolean): void {
        const origin = encodeURIComponent(window.location.origin);
        const iframe = document.createElement('iframe');
        iframe.src = `https://www.youtube.com/embed/${this.videoId}?autoplay=${autoplay ? 1 : 0}&playsinline=1&enablejsapi=1&origin=${origin}`;
        iframe.title = this.container.dataset.youtubeTitle ?? 'YouTube video';
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
        iframe.allowFullscreen = true;
        iframe.className = 'absolute inset-0 h-full w-full rounded-media border-0';

        const onMessage = (event: MessageEvent): void => {
            if (event.source !== iframe.contentWindow || typeof event.data !== 'string') {
                return;
            }

            try {
                const data = JSON.parse(event.data);

                if (data.event === 'onError') {
                    window.removeEventListener('message', onMessage);
                    this.restorePoster();
                }

                /** Player state 1 means playing - the overlay has done its job. */
                if (data.info?.playerState === 1 || (data.event === 'onStateChange' && data.info === 1)) {
                    this.dissolveOverlay();
                }
            } catch {
                return;
            }
        };

        window.addEventListener('message', onMessage);

        iframe.addEventListener('load', () => {
            window.setTimeout(() => {
                iframe.contentWindow?.postMessage(
                    JSON.stringify({ event: 'listening', id: this.videoId }),
                    '*',
                );
            }, 300);
        });

        /**
         * An iframe nested inside an <a> is invalid and confuses
         * assistive tech, so the anchor is swapped for a neutral
         * wrapper while the player is active.
         */
        const wrapper = document.createElement('div');
        wrapper.className = this.container.className;
        wrapper.appendChild(iframe);

        if (! autoplay) {
            /**
             * Keep the Apple poster on top until playback starts. It
             * ignores pointer events, so the first tap reaches the
             * YouTube play button directly beneath it.
             */
            const overlay = document.createElement('div');
            overlay.className = 'pointer-events-none absolute inset-0 transition-opacity duration-500';
            overlay.setAttribute('aria-hidden', 'true');
            overlay.innerHTML = this.container.innerHTML;
            wrapper.appendChild(overlay);
            this.overlay = overlay;
        }

        this.container.replaceWith(wrapper);
        this.player = wrapper;
    }

    /**
     * Fades the poster overlay away once the video is playing.
     */
    private dissolveOverlay(): void {
        const overlay = this.overlay;
        if (!overlay) {
            return;
        }

        this.overlay = null;
        overlay.style.opacity = '0';
        window.setTimeout(() => overlay.remove(), 500);
    }

    /**
     * Puts the poster back and lets further clicks open YouTube in a
     * new tab, for players that refuse to start in this browser.
     */
    private restorePoster(): void {
        this.failed = true;
        this.player?.replaceWith(this.container);
        this.player = null;
        this.overlay = null;
        this.container.setAttribute('target', '_blank');
        this.container.setAttribute('rel', 'noopener');
    }
}
