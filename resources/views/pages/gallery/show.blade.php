@php
    /** Albums are shared as a link to a photo set, so the cover leads the preview. */
    $shareDescription = $album->description
        ?: $album->title.' - 브리즈번 주는교회 갤러리'
            .($album->event_date ? ' · '.$album->event_date->translatedFormat('Y년 n월 j일') : '');
@endphp

<x-layout.app :title="$album->title" :description="$shareDescription" :image="$album->coverUrl()">

    <x-ui.page-header kicker="갤러리 · Gallery" :title="$album->title">
        @if ($album->event_date){{ $album->event_date->translatedFormat('Y년 n월 j일') }}@if ($album->description) - @endif@endif{{ $album->description }}
    </x-ui.page-header>

    <section class="section-album-photos container-site pb-12 lg:pb-16">
        {{-- The lightbox counts the album, not the page: it fetches the
             remaining pages as it reaches them, so announcing the 24
             photos rendered here would read as though the album ended
             at the first screenful. --}}
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4"
             data-lightbox-gallery
             data-photo-total="{{ $photos->total() }}">
            <div class="contents" data-infinite-scroll>
                @foreach ($photos as $photo)
                    <a href="{{ $photo->url() }}" data-lightbox class="block overflow-hidden rounded-media">
                        <img
                            src="{{ $photo->thumbnailUrl() }}"
                            alt="{{ $photo->caption ?? $album->title }}"
                            class="aspect-square w-full object-cover"
                            loading="lazy"
                        >
                    </a>
                @endforeach

                @if ($photos->hasMorePages())
                    <div class="col-span-full py-6 text-center">
                        <a href="{{ $photos->nextPageUrl() }}" data-next-page class="text-caption font-bold text-accent hover:text-accent-700">
                            사진 더 보기 →
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-10">
            <a href="{{ route('gallery.index') }}" class="text-caption font-bold text-accent hover:text-accent-700">← 앨범 전체 보기</a>
        </div>
    </section>

</x-layout.app>
