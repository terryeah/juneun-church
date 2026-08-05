import { MobileNav } from './components/MobileNav';
import { YouTubeLazy } from './components/YouTubeLazy';
import { Lightbox } from './components/Lightbox';
import { InfiniteScroll } from './components/InfiniteScroll';
import { PhotoSlider } from './components/PhotoSlider';
import { GivingWeeks } from './components/GivingWeeks';
import { DateFieldFormat } from './components/DateFieldFormat';

/**
 * Main entry point.
 *
 * Every component is a progressive enhancement: the site remains fully
 * usable without JavaScript, so each component binds only when its root
 * element exists on the current page.
 */
document.addEventListener('DOMContentLoaded', (): void => {
    const navToggle = document.querySelector<HTMLButtonElement>('[data-mobile-nav-toggle]');
    if (navToggle) {
        new MobileNav(navToggle);
    }

    document
        .querySelectorAll<HTMLElement>('[data-youtube-lazy]')
        .forEach((element) => new YouTubeLazy(element));

    const gallery = document.querySelector<HTMLElement>('[data-lightbox-gallery]');
    if (gallery) {
        new Lightbox(gallery);
    }

    const scrollContainer = document.querySelector<HTMLElement>('[data-infinite-scroll]');
    if (scrollContainer) {
        new InfiniteScroll(scrollContainer);
    }

    const slider = document.querySelector<HTMLElement>('[data-photo-slider]');
    if (slider) {
        new PhotoSlider(slider);
    }

    const givingWeeks = document.querySelector<HTMLElement>('[data-giving-weeks]');
    if (givingWeeks) {
        new GivingWeeks(givingWeeks);
    }

    document
        .querySelectorAll<HTMLInputElement>('input[data-date-field]')
        .forEach((field) => new DateFieldFormat(field));

    void import('./components/Animations').then(({ Animations }) => new Animations());
});
