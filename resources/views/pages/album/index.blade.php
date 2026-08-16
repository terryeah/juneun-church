{{-- The page is called 앨범 now, but 갤러리 is what people search for and
     what the address used to say, so the description keeps the word. --}}
<x-layout.app
    :title="'앨범 · '.$kindLabel"
    :description="'브리즈번 주는교회의 '.$kindLabel.' 갤러리입니다. 예배와 교회 행사의 '.$kindLabel.' 앨범을 모았습니다.'"
>

    <x-ui.page-header kicker="주는교회의 순간들 · Moments" title="앨범" />

    {{-- The whole section is replaced when a chip is clicked, so the
         chips and their active state live inside it and need no
         rebinding, exactly as the 헌금 week picker works. --}}
    <section class="section-gallery-albums container-site pb-12 lg:pb-16" data-gallery-filter>

        {{-- Which kind of album is on show. --}}
        <div class="flex flex-wrap gap-2">
            @foreach ($kinds as $key => $label)
                <a href="{{ route('album.index', array_filter(['kind' => $key === \App\Models\Album::TYPE_PHOTO ? null : $key])) }}"
                   class="rounded-nav px-4 py-2 font-kr text-body-sm transition-colors {{ $key === $kind ? 'bg-navy text-cream' : 'bg-navy/5 text-navy hover:bg-navy/10' }}"
                   @if ($key === $kind) aria-current="page" @endif
                   data-gallery-chip>
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="mt-8 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($albums as $album)
                {{-- Asked once. An album with no cover of its own works
                     the answer out from its first photo, and in the
                     largest album that is 806 rows sorted - twice, if
                     the test and the tag each ask separately. --}}
                @php $cover = $album->coverUrl(); @endphp
                <a href="{{ route('album.show', $album) }}" class="group block" data-gallery-item>
                    @if ($cover)
                        {{-- A video album wears the same frame and the same
                             play control as 최근 예배 on the home page: 16:9
                             over navy rather than the 4:3 a photo album uses,
                             because a still frame is otherwise
                             indistinguishable from a photograph. --}}
                        <div @class([
                            'relative overflow-hidden rounded-media',
                            'aspect-video bg-navy-900' => $album->holdsVideos(),
                        ])>
                            <img
                                src="{{ $cover }}"
                                alt="{{ $album->title }}"
                                @class([
                                    'object-cover',
                                    'absolute inset-0 h-full w-full' => $album->holdsVideos(),
                                    'aspect-[4/3] w-full' => ! $album->holdsVideos(),
                                ])
                                loading="lazy"
                                referrerpolicy="no-referrer"
                            >
                            @if ($album->holdsVideos())
                                <x-ui.play-overlay />
                            @endif
                        </div>
                    @else
                        <x-ui.photo-placeholder :label="$album->title" class="aspect-[4/3] w-full" />
                    @endif
                    {{-- No 성도 전용 tag: the page is 성도 전용 in full, so a
                         tag on each card would only repeat what getting here
                         already said. --}}
                    <h2 class="mt-3 font-kr text-body font-medium group-hover:text-accent">{{ $album->title }}</h2>
                    <p class="mt-1 text-body-sm text-navy-400">
                        @if ($album->event_date){{ $album->event_date->translatedFormat('Y년 n월 j일') }} · @endif{{ $kindLabel }} {{ $album->itemCount() }}{{ $album->holdsVideos() ? '편' : '장' }}
                    </p>
                </a>
            @empty
                <p class="text-body-sm text-navy-400">등록된 {{ $kindLabel }} 앨범이 없습니다.</p>
            @endforelse
        </div>
        <div class="mt-8">
            {{ $albums->links() }}
        </div>
    </section>

</x-layout.app>
