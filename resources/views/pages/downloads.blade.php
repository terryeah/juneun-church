<x-layout.app title="자료실" description="브리즈번 주는교회 주보와 교회 문서를 내려받을 수 있습니다.">

    <x-ui.page-header kicker="교회 자료 · Downloads" title="자료실">
        {{-- Matches the green 사용 중 badge in 관리자 페이지: slate pill, 1px success border, success label. --}}
        <x-slot:badge>
            <span class="inline-flex items-center rounded-md border border-success bg-slate-900 px-2 py-0.5 font-kr text-xs font-medium text-success">성도 전용</span>
        </x-slot:badge>
        주일 주보와 교회에서 쓰는 서식을 내려받는 곳입니다.
    </x-ui.page-header>

    {{-- Nothing to show a guest when it is all held back: the tabs and
         an empty rule would only frame an absence. The whole section is
         replaced on a tab click, so the chips and their active state
         live inside it and need no rebinding. --}}
    @if ($files->isNotEmpty() || ! $hasRestricted)
    <section class="section-downloads container-site pb-12 lg:pb-16" data-downloads>
        <div class="flex flex-wrap gap-2">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('downloads', ['type' => $key]) }}"
                   class="rounded-nav px-4 py-2 font-kr text-body-sm transition-colors {{ $key === $tab ? 'bg-navy text-cream' : 'bg-navy/5 text-navy hover:bg-navy/10' }}"
                   @if ($key === $tab) aria-current="page" @endif
                   data-download-tab>
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <h2 class="mt-6 font-kr text-display-sm font-medium">{{ $tabs[$tab] }}</h2>
        <p class="mt-2 font-kr text-body-sm text-navy-400">
            {{ $tab === 'bulletins'
                ? '주일 예배 주보입니다. 셀 편성과 섬김이 명단, 헌금 내역이 실려 있습니다.'
                : '교회에서 쓰는 서식과 문서입니다. 필요한 것을 내려받아 사용해 주세요.' }}
        </p>

        <div class="mt-6 border-t-2 border-navy">
            @forelse ($files as $file)
                <a href="{{ $file->fileUrl() }}" class="group flex items-center justify-between gap-4 border-b border-line py-5" target="_blank" rel="noopener" aria-label="{{ $file->title }} PDF 열기 (새 창)" data-download-item>
                    <div>
                        <p class="text-caption text-navy-400">{{ $file->published_at->translatedFormat('Y년 n월 j일') }}</p>
                        <h3 class="mt-1 font-kr text-body font-medium group-hover:text-accent">{{ $file->title }}</h3>
                        @if (filled($file->description ?? null))
                            <p class="mt-1 font-kr text-body-sm text-navy-400">{{ $file->description }}</p>
                        @endif
                    </div>
                    <span class="shrink-0 text-caption font-bold text-accent group-hover:text-accent-700">PDF 보기 →</span>
                </a>
            @empty
                <p class="py-8 text-body-sm text-navy-400">등록된 자료가 없습니다.</p>
            @endforelse
        </div>
    </section>
    @endif

    @if ($hasRestricted)
        <x-ui.sign-in-required
            class="section-downloads-signup"
            kicker="성도 전용 · Members only"
            title="자료실은 로그인 후 보실 수 있습니다"
            body="주보와 교회 서식에는 셀 편성과 섬김이 명단처럼 성도의 정보가 담겨 있어 성도에게만 공개됩니다. 계정이 없으시면 가입을 신청해 주세요. 관리자가 교적부와 대조해 확인한 뒤 승인해 드립니다."
        />
    @endif

</x-layout.app>
