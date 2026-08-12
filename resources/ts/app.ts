import { MobileNav } from './components/MobileNav';
import { YouTubeLazy } from './components/YouTubeLazy';
import { Lightbox } from './components/Lightbox';
import { VideoModal } from './components/VideoModal';
import { InfiniteScroll } from './components/InfiniteScroll';
import { PhotoSlider } from './components/PhotoSlider';
import { SectionSwap } from './components/SectionSwap';
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

    /**
     * The scroller is built first so the lightbox can hold it: on a
     * paginated album the lightbox runs out of photos before the album
     * does, and asks it for the next page rather than wrapping around.
     */
    const scrollContainer = document.querySelector<HTMLElement>('[data-infinite-scroll]');
    const scroller = scrollContainer ? new InfiniteScroll(scrollContainer) : null;

    const gallery = document.querySelector<HTMLElement>('[data-lightbox-gallery]');
    if (gallery) {
        new Lightbox(gallery, scroller);
    }

    const videoGallery = document.querySelector<HTMLElement>('[data-video-gallery]');
    if (videoGallery) {
        new VideoModal(videoGallery);
    }

    const slider = document.querySelector<HTMLElement>('[data-photo-slider]');
    if (slider) {
        new PhotoSlider(slider);
    }

    const givingWeeks = document.querySelector<HTMLElement>('[data-giving-weeks]');
    if (givingWeeks) {
        new SectionSwap(givingWeeks, {
            root: '[data-giving-weeks]',
            chip: '[data-giving-week]',
            stagger: '[data-giving-category]',
        });
    }

    const downloads = document.querySelector<HTMLElement>('[data-downloads]');
    if (downloads) {
        new SectionSwap(downloads, {
            root: '[data-downloads]',
            chip: '[data-download-tab]',
            stagger: '[data-download-item]',
        });
    }

    const galleryFilter = document.querySelector<HTMLElement>('[data-gallery-filter]');
    if (galleryFilter) {
        new SectionSwap(galleryFilter, {
            root: '[data-gallery-filter]',
            chip: '[data-gallery-chip]',
            stagger: '[data-gallery-item]',
        });
    }

    document
        .querySelectorAll<HTMLInputElement>('input[data-date-field]')
        .forEach((field) => new DateFieldFormat(field));

    void import('./components/Animations').then(({ Animations }) => new Animations());
});
