/**
 * Infinite Scroll Component
 *
 * Watches a sentinel "load more" link and fetches the next page of the
 * paginated grid when it approaches the viewport, appending the new
 * items in place. Without JavaScript the link works as ordinary
 * pagination.
 */
export class InfiniteScroll {
    private container: HTMLElement;
    private loading: boolean = false;

    /**
     * Creates a new InfiniteScroll instance.
     *
     * @param container - Element carrying the data-infinite-scroll attribute
     */
    constructor(container: HTMLElement) {
        this.container = container;
        this.observe();
    }

    /**
     * The current sentinel link to the next page, if any.
     */
    private sentinel(): HTMLAnchorElement | null {
        return this.container.querySelector<HTMLAnchorElement>('[data-next-page]');
    }

    /**
     * Observes the sentinel and loads the next page when it nears view.
     */
    private observe(): void {
        const sentinel = this.sentinel();
        if (!sentinel) {
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                if (entries.some((entry) => entry.isIntersecting)) {
                    observer.disconnect();
                    void this.loadNextPage();
                }
            },
            { rootMargin: '400px' },
        );

        observer.observe(sentinel);
    }

    /**
     * Fetches the next page and splices its grid items into the DOM.
     */
    private async loadNextPage(): Promise<void> {
        const sentinel = this.sentinel();
        if (!sentinel || this.loading) {
            return;
        }

        this.loading = true;

        try {
            const response = await fetch(sentinel.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const html = await response.text();
            const parsed = new DOMParser().parseFromString(html, 'text/html');
            const incoming = parsed.querySelector<HTMLElement>('[data-infinite-scroll]');

            if (incoming) {
                sentinel.parentElement?.remove();
                Array.from(incoming.children).forEach((child) => {
                    this.container.appendChild(child);
                });
            }
        } finally {
            this.loading = false;
            this.observe();
        }
    }
}
