/**
 * YouTube Lazy Loader Component
 *
 * Renders a poster frame with a play button and swaps in the YouTube
 * iframe only when the visitor clicks. Without JavaScript the element
 * remains a plain link to YouTube.
 */
export class YouTubeLazy {
    private container: HTMLElement;
    private videoId: string;

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
            if (this.videoId === '') {
                return;
            }

            event.preventDefault();
            this.loadIframe();
        });
    }

    /**
     * Replaces the poster content with an autoplaying YouTube embed.
     */
    private loadIframe(): void {
        const iframe = document.createElement('iframe');
        iframe.src = `https://www.youtube-nocookie.com/embed/${this.videoId}?autoplay=1`;
        iframe.title = this.container.dataset.youtubeTitle ?? 'YouTube video';
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
        iframe.allowFullscreen = true;
        iframe.className = 'absolute inset-0 h-full w-full rounded-media border-0';

        this.container.innerHTML = '';
        this.container.appendChild(iframe);
    }
}
