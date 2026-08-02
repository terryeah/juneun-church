@props([
    'sermon',
])

{{-- Click-to-load YouTube embed. Without JavaScript this is a plain link.
     The play control follows the Apple video-player treatment: a frosted
     translucent circle with a white glyph over the poster frame. --}}
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
    <span class="absolute inset-0 bg-black/0 transition-colors duration-300 group-hover:bg-black/10" aria-hidden="true"></span>
    <span class="absolute inset-0 flex items-center justify-center">
        <span class="flex h-[4.25rem] w-[4.25rem] items-center justify-center rounded-full bg-black/45 text-white backdrop-blur-md transition-all duration-300 group-hover:scale-105 group-hover:bg-black/60">
            <svg class="ml-1 h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M8 5.14v13.72c0 .89.98 1.43 1.73.95l10.28-6.86a1.13 1.13 0 0 0 0-1.9L9.73 4.19A1.13 1.13 0 0 0 8 5.14z"/>
            </svg>
        </span>
    </span>
</a>
