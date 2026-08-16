@php
    /** Albums are shared as a link to a photo set, so the cover leads the preview. */
    $shareDescription = $album->description
        ?: $album->title.' - 브리즈번 주는교회 사진 갤러리'
            .($album->event_date ? ' · '.$album->event_date->translatedFormat('Y년 n월 j일') : '');
@endphp

{{-- Always noindex: 앨범 is 성도 전용 as a whole section, so no album
     page is ever a page a crawler may keep. --}}

<x-layout.app :title="$album->title" :description="$shareDescription" :image="$album->coverUrl()" :noindex="true">

    <x-ui.page-header kicker="주는교회의 순간들 · Photos" :title="$album->title">
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
                @forelse ($photos as $photo)
                    {{-- The full-size dimensions let the lightbox lay the
                         photo out before it has one: it opens on the
                         thumbnail, and without knowing where the original
                         will land it drew that at the thumbnail's own size
                         and then jumped when the original replaced it. --}}
                    <a href="{{ $photo->url() }}" data-lightbox
                       @if ($photo->width && $photo->height)
                           data-width="{{ $photo->width }}" data-height="{{ $photo->height }}"
                       @endif
                       class="block overflow-hidden rounded-media">
                        <img
                            src="{{ $photo->thumbnailUrl() }}"
                            alt="{{ $photo->caption ?? $album->title }}"
                            class="aspect-square w-full object-cover"
                            loading="lazy"
                        >
                    </a>
                @empty
                    {{-- An album is usually made before the photographs
                         go in, and it is published from the moment it is
                         made, so this is an ordinary state. --}}
                    <p class="col-span-full font-kr text-body-sm text-navy-400">아직 올라온 사진이 없습니다.</p>
                @endforelse

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
            <a href="{{ route('album.index') }}" class="text-caption font-bold text-accent hover:text-accent-700">← 앨범 전체 보기</a>
        </div>
    </section>

</x-layout.app>
