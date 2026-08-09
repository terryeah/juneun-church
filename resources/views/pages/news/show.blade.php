@php
    /** The featured image doubles as the KakaoTalk / search thumbnail. */
    $shareImage = $announcement->featured_image
        ? Illuminate\Support\Facades\Storage::disk(config('filesystems.media'))->url($announcement->featured_image)
        : null;
@endphp

<x-layout.app :title="$announcement->title" :description="$announcement->excerpt(155)" :image="$shareImage">

    <article class="section-news-article container-site py-12 lg:py-16">
        <div class="mx-auto max-w-3xl">
            <x-ui.kicker>교회 소식 · News</x-ui.kicker>
            <h1 class="mt-3 font-kr text-display-md font-medium">{{ $announcement->title }}@if ($announcement->is_members_only)<span class="ml-2 inline-flex items-center rounded-md border border-success bg-slate-900 px-2 py-0.5 align-middle font-kr text-xs font-medium text-success">성도 전용</span>@endif</h1>
            <p class="mt-3 text-body-sm text-navy-400">{{ $announcement->published_at?->translatedFormat('Y년 n월 j일') }}</p>

            {{-- No forced ratio: a portrait poster shows in full rather than
                 being cropped to a letterbox. The measured width and height
                 give the browser the poster's own ratio, so the box is
                 reserved at its true shape and the article below never
                 jumps once the image arrives. --}}
            @if ($announcement->featured_image)
                @php $featuredSize = $announcement->featuredImageDimensions(); @endphp
                <img
                    @if ($featuredSize) width="{{ $featuredSize['width'] }}" height="{{ $featuredSize['height'] }}" @endif
                    src="{{ Illuminate\Support\Facades\Storage::disk(config('filesystems.media'))->url($announcement->featured_image) }}"
                    alt="{{ $announcement->title }}"
                    class="mt-8 h-auto w-full rounded-media"
                    fetchpriority="high"
                >
            @endif

            <div class="prose-announcement mt-8 font-kr text-body leading-relaxed text-navy-700 [&_a]:text-accent [&_a]:underline [&_h2]:mt-6 [&_h2]:font-medium [&_h2]:text-navy [&_h3]:mt-4 [&_h3]:font-medium [&_h3]:text-navy [&_p]:mt-4">
                {!! \Mews\Purifier\Facades\Purifier::clean($announcement->content) !!}
            </div>

            <div class="mt-10">
                <a href="{{ route('news.index') }}" class="text-caption font-bold text-accent hover:text-accent-700">← 소식 전체 보기</a>
            </div>
        </div>
    </article>

</x-layout.app>
