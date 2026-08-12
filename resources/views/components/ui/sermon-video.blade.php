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
    <x-ui.play-overlay />
</a>
