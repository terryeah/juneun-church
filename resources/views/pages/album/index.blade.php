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

        {{-- Which kind of album is on show. These lead, because they
             divide the page in two; the audience chips below only
             narrow whichever half is open. --}}
        <div class="flex flex-wrap gap-2">
            @foreach ($kinds as $key => $label)
                {{-- The audience filter survives a change of kind, the
                     same way the kind survives a change of filter. --}}
                <a href="{{ route('album.index', array_filter(['kind' => $key === \App\Models\Album::TYPE_PHOTO ? null : $key, 'visibility' => $filter === 'all' ? null : $filter])) }}"
                   class="rounded-nav px-4 py-2 font-kr text-body-sm transition-colors {{ $key === $kind ? 'bg-navy text-cream' : 'bg-navy/5 text-navy hover:bg-navy/10' }}"
                   @if ($key === $kind) aria-current="page" @endif
                   data-gallery-chip>
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Only a 성도 sees both kinds of album, so only a 성도 has
             anything to filter between. --}}
        @if (auth()->user()?->isChurchMember())
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($filters as $key => $label)
                    <a href="{{ route('album.index', array_filter(['kind' => $kind === \App\Models\Album::TYPE_PHOTO ? null : $kind, 'visibility' => $key === 'all' ? null : $key])) }}"
                       class="rounded-nav px-3 py-1.5 font-kr text-body-sm transition-colors {{ $key === $filter ? 'bg-navy text-cream' : 'bg-navy/5 text-navy hover:bg-navy/10' }}"
                       @if ($key === $filter) aria-current="page" @endif
                       data-gallery-chip>
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        @endif

        <div class="mt-8 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($albums as $album)
                {{-- Asked once. An album with no cover of its own works
                     the answer out from its first photo, and in the
                     largest album that is 806 rows sorted - twice, if
                     the test and the tag each ask separately. --}}
                @php $cover = $album->coverUrl(); @endphp
                <a href="{{ route('album.show', $album) }}" class="group block" data-gallery-item>
                    @if ($cover)
                        <div class="relative overflow-hidden rounded-media">
                            <img src="{{ $cover }}" alt="{{ $album->title }}" class="aspect-[4/3] w-full object-cover" loading="lazy" referrerpolicy="no-referrer">
                            {{-- A still frame is indistinguishable from a photograph,
                                 so a video album says so before it is opened. --}}
                            @if ($album->holdsVideos())
                                <span class="absolute inset-0 flex items-center justify-center bg-navy-900/25 transition-colors group-hover:bg-navy-900/40" aria-hidden="true">
                                    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-paper/90">
                                        <svg class="ml-1 h-6 w-6 text-navy" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13l11-6.5z"/></svg>
                                    </span>
                                </span>
                            @endif
                        </div>
                    @else
                        <x-ui.photo-placeholder :label="$album->title" class="aspect-[4/3] w-full" />
                    @endif
                    {{-- Same badge as the 교회 소식 list, sat inline after the title so it
                         follows the last word and a title that wraps on a phone leaves
                         the card layout alone. Only signed-in 성도 ever reach this. --}}
                    <h2 class="mt-3 font-kr text-body font-medium group-hover:text-accent">{{ $album->title }}@if ($album->is_members_only)<span class="ml-2 inline-flex items-center rounded-md border border-success bg-slate-900 px-2 py-0.5 align-middle font-kr text-xs font-medium text-success">성도 전용</span>@endif</h2>
                    <p class="mt-1 text-body-sm text-navy-400">
                        @if ($album->event_date){{ $album->event_date->translatedFormat('Y년 n월 j일') }} · @endif{{ $kindLabel }} {{ $album->itemCount() }}{{ $album->holdsVideos() ? '편' : '장' }}
                    </p>
                </a>
            @empty
                {{-- With a chip active the grid is empty because of the
                     filter, not because the album shelf is. --}}
                <p class="text-body-sm text-navy-400">{{ $filter === 'all' ? '등록된 '.$kindLabel.' 앨범이 없습니다.' : '해당하는 앨범이 없습니다.' }}</p>
            @endforelse
        </div>
        <div class="mt-8">
            {{ $albums->links() }}
        </div>
    </section>

</x-layout.app>
