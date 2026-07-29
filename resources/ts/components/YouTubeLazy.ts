/**
 * YouTube Lazy Loader Component
 *
 * Renders a poster frame with a play button and swaps in the YouTube
 * iframe only when the visitor clicks. Without JavaScript the element
 * remains a plain link to YouTube. If the player reports an error -
 * an embed-restricted upload, or a browser that blocks the embed's
 * storage - the poster is restored and the click falls through to
 * YouTube in a new tab instead of leaving a dead player.
 */
export class YouTubeLazy {
    private container: HTMLElement;
    private videoId: string;
    private posterHtml: string = '';
    private failed: boolean = false;

    /**
     * Creates a new YouTubeLazy instance.
     *
     * @param container - Element carrying the data-youtube-lazy attribute
     */
    constructor(container: HTMLElement) {
        this.container = container;
        this.videoId = container.dataset.youtubeLazy ?? '';
        this.bindEvents();
    }

    /**
     * Intercepts the click on the poster link and loads the iframe.
     */
    private bindEvents(): void {
        this.container.addEventListener('click', (event: Event) => {
            if (this.videoId === '' || this.failed) {
                return;
            }

            event.preventDefault();
            this.loadIframe();
        });
    }

    /**
     * Replaces the poster content with an autoplaying YouTube embed
     * and subscribes to player API events so failures are detected.
     */
    private loadIframe(): void {
        const origin = encodeURIComponent(window.location.origin);
        const iframe = document.createElement('iframe');
        iframe.src = `https://www.youtube.com/embed/${this.videoId}?autoplay=1&playsinline=1&enablejsapi=1&origin=${origin}`;
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

        this.posterHtml = this.container.innerHTML;
        this.container.innerHTML = '';
        this.container.appendChild(iframe);
    }

    /**
     * Puts the poster back and lets further clicks open YouTube in a
     * new tab, for players that refuse to start in this browser.
     */
    private restorePoster(): void {
        this.failed = true;
        this.container.innerHTML = this.posterHtml;
        this.container.setAttribute('target', '_blank');
        this.container.setAttribute('rel', 'noopener');
    }
}
