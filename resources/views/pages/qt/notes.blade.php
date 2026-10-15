<x-layout.app title="내 묵상 노트" description="큐티를 하며 남긴 나의 묵상입니다." :noindex="true">

    <x-ui.page-header kicker="말씀과 함께 · QT" title="내 묵상 노트">
        큐티를 하며 남기신 묵상입니다. 본인만 볼 수 있습니다.
    </x-ui.page-header>

    <section class="section-qt-notes container-site pb-12 lg:pb-16">
        <div class="flex justify-end">
            <a href="{{ route('qt.index') }}" class="font-kr text-body-sm font-bold text-accent hover:text-accent-700">← 큐티 목록</a>
        </div>

        <div class="mt-3 border-t-2 border-navy pt-3 lg:pt-5">
            @forelse ($notes as $note)
                <article class="py-6">
                    <p class="text-caption text-navy-400">{{ $note->devotion->devotion_date->translatedFormat('Y년 n월 j일 (D)') }} · {{ $note->devotion->scripture }}</p>
                    <h2 class="mt-1 font-kr text-display-sm font-medium">
                        <a href="{{ route('qt.show', $note->devotion) }}" class="hover:text-accent">{{ $note->devotion->title }}</a>
                    </h2>
                    <p class="mt-3 max-w-3xl whitespace-pre-line font-kr text-body leading-relaxed text-navy-700">{{ $note->body }}</p>
                </article>
            @empty
                <p class="py-8 text-body-sm text-navy-400">아직 남긴 묵상이 없습니다. <a href="{{ route('qt.index') }}" class="font-bold text-accent hover:text-accent-700">오늘의 큐티</a>부터 시작해 보세요.</p>
            @endforelse
        </div>
        <div class="mt-8">
            {{ $notes->links() }}
        </div>
    </section>

</x-layout.app>
