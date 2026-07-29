<x-layout.app title="예배 안내" description="브리즈번 주는교회 예배 시간과 지난 예배 영상을 안내합니다.">

    <x-ui.page-header kicker="예배 안내 · Worship" title="함께 드리는 예배">
        주는교회의 모든 예배는 누구에게나 열려 있습니다. 이번 주, 함께 예배해요.
    </x-ui.page-header>

    <section class="section-worship-services container-site">
        <x-home.service-strip />
    </section>

    <section class="section-worship-archive container-site pt-8 pb-12 lg:pt-10 lg:pb-16">
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
    </section>

</x-layout.app>
