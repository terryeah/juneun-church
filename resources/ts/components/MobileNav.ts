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
        this.menu?.classList.toggle('hidden', !open);
        document.body.classList.toggle('overflow-hidden', open);
    }
}
