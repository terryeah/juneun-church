<x-layout.app title="예배 안내" description="브리즈번 주는교회 예배 시간과 최근 예배 영상을 안내합니다.">

    <x-ui.page-header kicker="Worship · 예배 안내" title="함께 드리는 예배">
        주는교회의 모든 예배는 누구에게나 열려 있습니다. 이번 주, 함께 예배해요.
    </x-ui.page-header>

    @if ($featured)
        <section class="container-site py-10 lg:py-14">
            <div class="grid gap-8 lg:grid-cols-[1.3fr_1fr] lg:gap-11">
                <x-ui.sermon-video :sermon="$featured" />
                <div class="lg:self-center">
                    <x-ui.kicker>최근 예배 · Latest</x-ui.kicker>
                    <h2 class="mt-3 font-kr text-display-md font-medium">{{ $featured->title }}</h2>
                    <p class="mt-3 text-[13px] text-navy-400">
                        {{ $featured->preacher }} · {{ $featured->sermon_date->translatedFormat('Y년 n월 j일') }}
                        · {{ $featured->serviceType?->name }}
                    </p>
                    @if ($featured->scripture_reference)
                        <p class="mt-2 text-[11px] font-bold tracking-[0.08em] text-accent">{{ $featured->scripture_reference }}</p>
                    @endif
                    @if ($featured->description)
                        <p class="mt-4 font-kr text-[13.5px] leading-relaxed text-navy-700">{{ $featured->description }}</p>
                    @endif
                </div>
            </div>
        </section>
    @endif

    <section class="container-site py-10 lg:py-14">
        <x-ui.kicker>지난 예배 · Archive</x-ui.kicker>
        <div class="mt-6 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($sermons as $sermon)
                <div>
                    <x-ui.sermon-video :sermon="$sermon" />
                    <h3 class="mt-3 font-kr text-[15px] font-medium">{{ $sermon->title }}</h3>
                    <p class="mt-1 text-[12px] text-navy-400">
                        {{ $sermon->sermon_date->translatedFormat('Y년 n월 j일') }} · {{ $sermon->serviceType?->name }}
                    </p>
                </div>
            @empty
                <p class="text-[13px] text-navy-400">등록된 예배 영상이 없습니다.</p>
            @endforelse
        </div>
        <div class="mt-8">
            {{ $sermons->links() }}
        </div>
    </section>

</x-layout.app>
