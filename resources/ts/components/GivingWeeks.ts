/**
 * Giving Weeks Component
 *
 * Swaps only the weekly offering records section when a week chip is
 * clicked, leaving the identical bank details above it untouched. The
 * chips remain ordinary links, so without JavaScript each one is a
 * normal full page navigation.
 */
export class GivingWeeks {
    private section: HTMLElement;

    /**
     * Creates a new GivingWeeks instance.
     *
     * @param section - Element carrying the data-giving-weeks attribute
     */
    constructor(section: HTMLElement) {
        this.section = section;

        /**
         * Clicks are handled on the document rather than on each chip so
         * that a swapped-in set of chips needs no rebinding.
         */
        document.addEventListener('click', (event: MouseEvent) => this.onClick(event));
        window.addEventListener('popstate', () => void this.swap(window.location.href, false));
    }

    /**
     * Intercepts a plain left click on a week chip inside the section.
     *
     * @param event - The originating click event
     */
    private onClick(event: MouseEvent): void {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        const chip = (event.target as Element | null)?.closest<HTMLAnchorElement>('[data-giving-week]');

        if (! chip || ! this.section.contains(chip)) {
            return;
        }

        event.preventDefault();
        void this.swap(chip.href, true);
    }

    /**
     * Fetches a week's page and replaces the records section with it.
     *
     * @param url - The week URL to render
     * @param push - Whether to add a history entry for the new week
     */
    private async swap(url: string, push: boolean): Promise<void> {
        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (! response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const parsed = new DOMParser().parseFromString(await response.text(), 'text/html');
            const incoming = parsed.querySelector<HTMLElement>('[data-giving-weeks]');

            if (! incoming) {
                throw new Error('Missing records section');
            }

            this.section.replaceWith(incoming);
            this.section = incoming;

            if (push) {
                window.history.pushState({}, '', url);
            }
        } catch {
            /**
             * Anything unexpected falls back to the plain navigation the
             * chip would have performed on its own.
             */
            window.location.href = url;
        }
    }
}
