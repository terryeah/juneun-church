<x-layout.app title="주보" description="브리즈번 주는교회 주보를 확인하고 내려받을 수 있습니다.">

    <x-ui.page-header kicker="주일의 기록 · Bulletin" title="주보" />

    @if ($bulletins->isNotEmpty())
        <section class="section-bulletin-list container-site pb-12 lg:pb-16">
            <div class="border-t-2 border-navy">
                @foreach ($bulletins as $bulletin)
                    <a href="{{ $bulletin->fileUrl() }}" class="group flex items-center justify-between gap-4 border-b border-line py-5" target="_blank" rel="noopener" aria-label="{{ $bulletin->title }} 주보 PDF 열기 (새 창)">
                        <div>
                            <p class="text-caption text-navy-400">{{ $bulletin->published_at->translatedFormat('Y년 n월 j일') }}</p>
                            <h2 class="mt-1 font-kr text-body font-medium group-hover:text-accent">{{ $bulletin->title }}</h2>
                        </div>
                        <span class="shrink-0 text-caption font-bold text-accent group-hover:text-accent-700">PDF 보기 →</span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8">
                {{ $bulletins->links() }}
            </div>
        </section>
    @elseif (! $hasRestricted)
        <section class="section-bulletin-list container-site pb-12 lg:pb-16">
            <div class="border-t-2 border-navy">
                <p class="py-8 text-body-sm text-navy-400">등록된 주보가 없습니다.</p>
            </div>
        </section>
    @endif

    {{-- Shown only when something really is being held back, so a guest
         is never told to sign in for bulletins they can already read. --}}
    @if ($hasRestricted)
        <x-ui.sign-in-required
            class="section-bulletin-signup"
            kicker="주일의 기록 · Bulletin"
            title="주보는 로그인 후 보실 수 있습니다"
            body="주보에는 셀 편성과 섬김이 명단, 헌금 내역처럼 성도의 정보가 담겨 있어 성도에게만 공개됩니다. 계정이 없으시면 가입을 신청해 주세요. 관리자가 교적부와 대조해 확인한 뒤 승인해 드립니다."
        />
    @endif

</x-layout.app>
