@php
    /** Albums are shared as a link to a set, so the first still leads the preview. */
    $shareDescription = $album->description
        ?: $album->title.' - 브리즈번 주는교회 동영상'
            .($album->event_date ? ' · '.$album->event_date->translatedFormat('Y년 n월 j일') : '');
@endphp

<x-layout.app :title="$album->title" :description="$shareDescription" :image="$album->coverUrl()">

    <x-ui.page-header kicker="주는교회의 순간들 · Videos" :title="$album->title">
        @if ($album->event_date){{ $album->event_date->translatedFormat('Y년 n월 j일') }}@if ($album->description) - @endif@endif{{ $album->description }}
    </x-ui.page-header>

    <section class="section-album-videos container-site pb-12 lg:pb-16">
        {{-- Each card carries everything the player needs, so opening a
             video writes one frame into the page and nothing about
             YouTube is loaded until then. --}}
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-video-gallery>
            @foreach ($videos as $video)
                <button
                    type="button"
                    class="group block w-full text-left"
                    data-video
                    data-video-embed="{{ $video->embedUrl() }}"
                    data-video-title="{{ $video->title }}"
                >
                    <span class="relative block overflow-hidden rounded-media">
                        <img
                            src="{{ $video->thumbnailUrl() }}"
                            alt=""
                            class="aspect-video w-full object-cover"
                            loading="lazy"
                            referrerpolicy="no-referrer"
                        >
                        <span class="absolute inset-0 flex items-center justify-center bg-navy-900/25 transition-colors group-hover:bg-navy-900/40" aria-hidden="true">
                            <span class="flex h-14 w-14 items-center justify-center rounded-full bg-paper/90">
                                <svg class="ml-1 h-6 w-6 text-navy" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13l11-6.5z"/></svg>
                            </span>
                        </span>
                    </span>
                    <span class="mt-3 block font-kr text-body font-medium group-hover:text-accent">{{ $video->title }}</span>
                    @if ($video->description)
                        <span class="mt-1 block font-kr text-body-sm text-navy-400">{{ $video->description }}</span>
                    @endif
                </button>
            @endforeach
        </div>

        @if ($videos->hasPages())
            <div class="mt-8">
                {{ $videos->links() }}
            </div>
        @endif

        <div class="mt-10">
            <a href="{{ route('album.index', ['kind' => \App\Models\Album::TYPE_VIDEO]) }}" class="text-caption font-bold text-accent hover:text-accent-700">← 앨범 전체 보기</a>
        </div>
    </section>

</x-layout.app>
