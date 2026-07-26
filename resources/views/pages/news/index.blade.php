<x-layout.app title="교회 소식" description="브리즈번 주는교회의 소식과 공지사항입니다.">

    <x-ui.page-header kicker="News · 교회 소식" title="교회 소식" />

    <section class="container-site pb-12 lg:pb-16">
        <div class="border-t-2 border-navy">
            @forelse ($announcements as $announcement)
                <a href="{{ route('news.show', $announcement) }}" class="group block border-b border-line py-5">
                    <p class="text-[11px] text-navy-400">
                        {{ $announcement->published_at?->translatedFormat('Y년 n월 j일') }}
                        @if ($announcement->is_pinned)
                            <span class="ml-2 font-extrabold uppercase tracking-[0.16em] text-accent">Pinned</span>
                        @endif
                    </p>
                    <h2 class="mt-1 font-kr text-display-sm font-medium group-hover:text-accent">{{ $announcement->title }}</h2>
                    <p class="mt-2 line-clamp-2 max-w-2xl font-kr text-[13.5px] leading-relaxed text-navy-700">
                        {{ str($announcement->content)->stripTags()->limit(140) }}
                    </p>
                </a>
            @empty
                <p class="py-8 text-[13px] text-navy-400">등록된 소식이 없습니다.</p>
            @endforelse
        </div>
        <div class="mt-8">
            {{ $announcements->links() }}
        </div>
    </section>

</x-layout.app>
