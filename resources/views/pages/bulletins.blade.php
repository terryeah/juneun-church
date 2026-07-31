<x-layout.app title="주보" description="브리즈번 주는교회 주보를 확인하고 내려받을 수 있습니다.">

    <x-ui.page-header kicker="주보 · Bulletin" title="주보" />

    <section class="section-bulletin-list container-site pb-12 lg:pb-16">
        <div class="border-t-2 border-navy">
            @forelse ($bulletins as $bulletin)
                <a href="{{ $bulletin->fileUrl() }}" class="group flex items-center justify-between gap-4 border-b border-line py-5" target="_blank" rel="noopener" aria-label="{{ $bulletin->title }} 주보 PDF 열기 (새 창)">
                    <div>
                        <p class="text-[11px] text-navy-400">{{ $bulletin->published_at->translatedFormat('Y년 n월 j일') }}</p>
                        <h2 class="mt-1 font-kr text-[15px] font-medium group-hover:text-accent">{{ $bulletin->title }}</h2>
                    </div>
                    <span class="shrink-0 text-[11px] font-bold text-accent group-hover:text-accent-700">PDF 보기 →</span>
                </a>
            @empty
                <p class="py-8 text-[13px] text-navy-400">등록된 주보가 없습니다.</p>
            @endforelse
        </div>
        <div class="mt-8">
            {{ $bulletins->links() }}
        </div>
    </section>

</x-layout.app>
