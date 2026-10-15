<x-layout.app title="큐티" description="주일 말씀으로 이어가는 한 주간의 큐티입니다." :noindex="true">

    <x-ui.page-header kicker="말씀과 함께 · QT" title="큐티">
        주일 말씀을 한 주간 이어서 묵상합니다. 남기신 묵상은 본인만 볼 수 있습니다.
    </x-ui.page-header>

    <section class="section-qt-list container-site pb-12 lg:pb-16">
        <div class="flex justify-end">
            <a href="{{ route('qt.notes') }}" class="font-kr text-body-sm font-bold text-accent hover:text-accent-700">내 묵상 노트 →</a>
        </div>

        <div class="mt-3 border-t-2 border-navy pt-3 lg:pt-5">
            @forelse ($devotions as $devotion)
                <a href="{{ route('qt.show', $devotion) }}" class="group block py-5">
                    <p class="text-caption text-navy-400">
                        {{ $devotion->devotion_date->translatedFormat('Y년 n월 j일 (D)') }}
                        @if ($devotion->devotion_date->isToday())
                            <span class="ml-2 font-extrabold uppercase tracking-[0.16em] text-accent">Today</span>
                        @endif
                        @if (in_array($devotion->id, $written, true))
                            <span class="ml-2 font-bold text-navy">✓ 묵상함</span>
                        @endif
                    </p>
                    <h2 class="mt-1 font-kr text-display-sm font-medium group-hover:text-accent">{{ $devotion->title }}</h2>
                    <p class="mt-1 font-kr text-body-sm text-navy-700">{{ $devotion->scripture }}</p>
                </a>
            @empty
                <p class="py-8 text-body-sm text-navy-400">아직 올라온 큐티가 없습니다.</p>
            @endforelse
        </div>
        <div class="mt-8">
            {{ $devotions->links() }}
        </div>
    </section>

</x-layout.app>
