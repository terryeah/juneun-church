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
    private failures: number = 0;
    private status: HTMLElement;

    /**
     * Creates a new InfiniteScroll instance.
     *
     * @param container - Element carrying the data-infinite-scroll attribute
     */
    constructor(container: HTMLElement) {
        this.container = container;
        this.status = document.createElement('span');
        this.status.setAttribute('role', 'status');
        this.status.className = 'sr-only';
        container.after(this.status);
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
        this.status.textContent = '사진을 불러오는 중';

        try {
            const response = await fetch(sentinel.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (! response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const html = await response.text();
            const parsed = new DOMParser().parseFromString(html, 'text/html');
            const incoming = parsed.querySelector<HTMLElement>('[data-infinite-scroll]');

            if (incoming) {
                sentinel.parentElement?.remove();
                const count = incoming.children.length;
                Array.from(incoming.children).forEach((child) => {
                    this.container.appendChild(child);
                });
                this.status.textContent = `사진 ${count}장이 추가되었습니다`;
            }

            this.failures = 0;
        } catch {
            /**
             * A dead next page must not retry forever; after two
             * failures the sentinel stays as an ordinary link.
             */
            this.failures += 1;
            this.status.textContent = '';

            if (this.failures >= 2) {
                this.loading = false;

                return;
            }
        } finally {
            this.loading = false;
        }

        this.observe();
    }
}
