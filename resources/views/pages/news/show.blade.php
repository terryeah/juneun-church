<x-layout.app :title="$announcement->title">

    <article class="section-news-article container-site py-12 lg:py-16">
        <div class="mx-auto max-w-3xl">
            <x-ui.kicker>뉴스 · News</x-ui.kicker>
            <h1 class="mt-3 font-kr text-display-md font-medium">{{ $announcement->title }}</h1>
            <p class="mt-3 text-[12px] text-navy-400">{{ $announcement->published_at?->translatedFormat('Y년 n월 j일') }}</p>

            @if ($announcement->featured_image)
                <img
                    src="{{ Illuminate\Support\Facades\Storage::disk(config('filesystems.media'))->url($announcement->featured_image) }}"
                    alt="{{ $announcement->title }}"
                    class="mt-8 w-full rounded-media object-cover"
                >
            @endif

            <div class="prose-announcement mt-8 font-kr text-[15px] leading-relaxed text-navy-700 [&_a]:text-accent [&_a]:underline [&_h2]:mt-6 [&_h2]:font-medium [&_h2]:text-navy [&_h3]:mt-4 [&_h3]:font-medium [&_h3]:text-navy [&_p]:mt-4">
                {!! $announcement->content !!}
            </div>

            <div class="mt-10">
                <a href="{{ route('news.index') }}" class="text-[11px] font-bold text-accent hover:text-accent-700">← 뉴스 전체 보기</a>
            </div>
        </div>
    </article>

</x-layout.app>
