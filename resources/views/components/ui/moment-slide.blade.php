@props([
    'photo',
])

@php
    /**
     * A slide links through to its album only when that album is on 앨범.
     * Six albums are kept off it while still holding pictures the site
     * uses elsewhere, and their detail page answers 404 to everybody -
     * a 성도 included - so a link there would be a dead end.
     */
    $listed = (bool) $photo->album?->is_published;
    $classes = 'block overflow-hidden rounded-[1.35rem]';
@endphp

@if ($listed)
    <a href="{{ route('album.show', $photo->album) }}" class="{{ $classes }}">
@else
    <div class="{{ $classes }}">
@endif
    <img src="{{ $photo->thumbnailUrl() }}" alt="{{ $photo->caption ?? $photo->album?->title }}" class="aspect-square w-full object-cover" loading="lazy">
@if ($listed)
    </a>
@else
    </div>
@endif
