@props([
    'sermon',
])

{{-- Click-to-load YouTube embed. Without JavaScript this is a plain link.
     The play control follows the Apple video treatment: a bare white
     rounded triangle over a dimmed poster frame, no circle. --}}
<a
    href="{{ $sermon->youtubeUrl() }}"
    class="group relative block aspect-video overflow-hidden rounded-media bg-navy-900"
    data-youtube-lazy="{{ $sermon->youtube_video_id }}"
    data-youtube-title="{{ $sermon->title }}"
>
    <img
        src="{{ $sermon->thumbnailUrl() }}"
        alt="{{ $sermon->title }}"
        class="absolute inset-0 h-full w-full object-cover"
        loading="lazy"
    >
    <span class="absolute inset-0 bg-black/30 transition-colors duration-300 group-hover:bg-black/40" aria-hidden="true"></span>
    <span class="absolute inset-0 flex items-center justify-center">
        <svg class="h-16 w-16 text-white drop-shadow-[0_0.125rem_0.5rem_rgba(0,0,0,0.35)] transition-transform duration-300 group-hover:scale-105" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M8.5 6.2v11.6l9.8-5.8z" fill="currentColor" stroke="currentColor" stroke-width="2.6" stroke-linejoin="round"/>
        </svg>
    </span>
</a>
