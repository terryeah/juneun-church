/**
 * Mobile Navigation Component
 *
 * Toggles the full-screen mobile menu and locks body scroll while the
 * menu is open, per the design handoff.
 */
export class MobileNav {
    private toggle: HTMLButtonElement;
    private menu: HTMLElement | null;

    /**
     * Creates a new MobileNav instance.
     *
     * @param toggle - The hamburger button element
     */
    constructor(toggle: HTMLButtonElement) {
        this.toggle = toggle;
        this.menu = document.querySelector<HTMLElement>('[data-mobile-nav-menu]');
        this.bindEvents();
    }

    /**
     * Binds click and escape-key handlers.
     */
    private bindEvents(): void {
        this.toggle.addEventListener('click', () => this.setOpen(!this.isOpen()));

        document.addEventListener('keydown', (event: KeyboardEvent) => {
            if (event.key === 'Escape' && this.isOpen()) {
                this.setOpen(false);
            }
        });

        window.matchMedia('(min-width: 1024px)').addEventListener('change', (event) => {
            if (event.matches && this.isOpen()) {
                this.setOpen(false);
            }
        });
    }

    /**
     * Whether the menu is currently open.
     */
    private isOpen(): boolean {
        return this.toggle.getAttribute('aria-expanded') === 'true';
    }

    /**
     * Opens or closes the menu and synchronises scroll lock state.
     *
     * @param open - The desired menu state
     */
    private setOpen(open: boolean): void {
        this.toggle.setAttribute('aria-expanded', String(open));
        this.toggle.setAttribute('aria-label', open ? '메뉴 닫기' : '메뉴 열기');
        document.body.classList.toggle('overflow-hidden', open);

        if (open) {
            this.menu?.classList.remove('hidden');
            document.dispatchEvent(new CustomEvent('mobilenav:opened'));
        } else {
            void this.hideWhenFinished();
        }

        /**
         * The open menu overlays the page, so everything outside it is
         * made inert for keyboard and screen reader users. The header
         * stays live because it contains the toggle button itself.
         */
        requestAnimationFrame(() => {
            document.querySelectorAll<HTMLElement>('main, footer').forEach((region) => {
                region.toggleAttribute('inert', open);
            });

            if (open) {
                this.menu?.querySelector<HTMLElement>('a, button')?.focus({ preventScroll: true });
            } else {
                this.toggle.focus({ preventScroll: true });
            }
        });
    }

    /**
     * Hides the menu once anything animating it has finished.
     *
     * Listeners push a promise onto the event's `animations` array to
     * hold the menu open long enough to play an exit; with nobody
     * listening - JavaScript for the animations not loaded yet, or a
     * visitor who prefers reduced motion - the array stays empty and
     * the menu closes at once, exactly as it used to.
     */
    private async hideWhenFinished(): Promise<void> {
        const animations: Promise<unknown>[] = [];

        document.dispatchEvent(new CustomEvent('mobilenav:closing', { detail: { animations } }));

        await Promise.all(animations);

        /** Reopening mid-exit wins: only hide if it is still closed. */
        if (! this.isOpen()) {
            this.menu?.classList.add('hidden');
        }
    }
}
