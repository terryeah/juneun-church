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
    <span class="absolute inset-0 bg-black/20 transition-colors duration-300 group-hover:bg-black/30" aria-hidden="true"></span>
    <span class="absolute inset-0 flex items-center justify-center">
        <svg class="h-[4.5rem] w-[4.5rem] text-white drop-shadow-[0_0.25rem_1rem_rgba(0,0,0,0.3)] transition-transform duration-300 ease-out group-hover:scale-[1.06]" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M9 6.6v10.8l9-5.4z" fill="currentColor" stroke="currentColor" stroke-width="3.4" stroke-linejoin="round" stroke-linecap="round"/>
        </svg>
    </span>
</a>
