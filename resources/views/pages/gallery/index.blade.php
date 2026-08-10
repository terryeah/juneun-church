<x-layout.app title="갤러리" description="브리즈번 주는교회의 활동 사진 앨범입니다.">

    <x-ui.page-header kicker="주는교회의 순간들 · Moments" title="갤러리" />

    {{-- The whole section is replaced when a chip is clicked, so the
         chips and their active state live inside it and need no
         rebinding, exactly as the 헌금 week picker works. --}}
    <section class="section-gallery-albums container-site pb-12 lg:pb-16" data-gallery-filter>
        {{-- Only a 성도 sees both kinds of album, so only a 성도 has
             anything to filter between. --}}
        @if (auth()->user()?->isChurchMember())
            <div class="mb-8 flex flex-wrap gap-2">
                @foreach ($filters as $key => $label)
                    <a href="{{ route('gallery.index', $key === 'all' ? [] : ['visibility' => $key]) }}"
                       class="rounded-nav px-3 py-1.5 font-kr text-body-sm transition-colors {{ $key === $filter ? 'bg-navy text-cream' : 'bg-navy/5 text-navy hover:bg-navy/10' }}"
                       @if ($key === $filter) aria-current="page" @endif
                       data-gallery-chip>
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        @endif

        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($albums as $album)
                <a href="{{ route('gallery.show', $album) }}" class="group block" data-gallery-item>
                    @if ($album->coverUrl())
                        <div class="overflow-hidden rounded-media"><img src="{{ $album->coverUrl() }}" alt="{{ $album->title }}" class="aspect-[4/3] w-full object-cover" loading="lazy"></div>
                    @else
                        <x-ui.photo-placeholder :label="$album->title" class="aspect-[4/3] w-full" />
                    @endif
                    {{-- Same badge as the 교회 소식 list, sat inline after the title so it
                         follows the last word and a title that wraps on a phone leaves
                         the card layout alone. Only signed-in 성도 ever reach this. --}}
                    <h2 class="mt-3 font-kr text-body font-medium group-hover:text-accent">{{ $album->title }}@if ($album->is_members_only)<span class="ml-2 inline-flex items-center rounded-md border border-success bg-slate-900 px-2 py-0.5 align-middle font-kr text-xs font-medium text-success">성도 전용</span>@endif</h2>
                    <p class="mt-1 text-body-sm text-navy-400">
                        @if ($album->event_date){{ $album->event_date->translatedFormat('Y년 n월 j일') }} · @endif사진 {{ $album->photos_count }}장
                    </p>
                </a>
            @empty
                {{-- With a chip active the grid is empty because of the
                     filter, not because the gallery is. --}}
                <p class="text-body-sm text-navy-400">{{ $filter === 'all' ? '등록된 앨범이 없습니다.' : '해당하는 앨범이 없습니다.' }}</p>
            @endforelse
        </div>
        <div class="mt-8">
            {{ $albums->links() }}
        </div>
    </section>

</x-layout.app>
