@props([
    'sermon',
])

{{-- Click-to-load YouTube embed. Without JavaScript this is a plain link. --}}
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
    <span class="absolute inset-0 flex items-center justify-center">
        <span class="flex h-[58px] w-[58px] items-center justify-center rounded-play bg-accent text-on-accent group-hover:bg-accent-700">
            <svg class="h-6 w-6 translate-x-[2px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M8 5.5v13l11-6.5z"/>
            </svg>
        </span>
    </span>
</a>
