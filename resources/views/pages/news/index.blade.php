<x-layout.app title="교회 소식" description="브리즈번 주는교회의 소식과 공지사항입니다.">

    <x-ui.page-header kicker="함께 나누는 소식 · News" title="교회 소식" />

    <section class="section-news-list container-site pb-12 lg:pb-16">
        <div class="border-t-2 border-navy pt-3 lg:pt-5">
            @forelse ($announcements as $announcement)
                <a href="{{ route('news.show', $announcement) }}" class="group block py-5">
                    <p class="text-caption text-navy-400">
                        {{ $announcement->published_at?->translatedFormat('Y년 n월 j일') }}
                        @if ($announcement->is_pinned)
                            <span class="ml-2 font-extrabold uppercase tracking-[0.16em] text-accent">Pinned</span>
                        @endif
                    </p>
                    {{-- Same badge as the 헌금 records, sat inline after the title so it
                         follows the last word and a title that wraps on a phone leaves
                         the list layout alone. Only signed-in 성도 ever reach this. --}}
                    <h2 class="mt-1 font-kr text-display-sm font-medium group-hover:text-accent">{{ $announcement->title }}@if ($announcement->is_members_only)<span class="ml-2 inline-flex items-center rounded-md border border-success bg-slate-900 px-2 py-0.5 align-middle font-kr text-xs font-medium text-success">성도 전용</span>@endif</h2>
                    <p class="mt-2 line-clamp-2 max-w-2xl font-kr text-body-sm leading-relaxed text-navy-700">
                        {{ $announcement->excerpt() }}
                    </p>
                </a>
            @empty
                <p class="py-8 text-body-sm text-navy-400">등록된 소식이 없습니다.</p>
            @endforelse
        </div>
        <div class="mt-8">
            {{ $announcements->links() }}
        </div>
    </section>

</x-layout.app>
