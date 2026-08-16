@php
    $date = $bulletin->published_at->translatedFormat('Y년 n월 j일');
@endphp

{{-- 성도 전용 for the most part, and a page about one week besides, so
     it stays out of search results. --}}
<x-layout.app :title="'주보 · '.$date" :description="$date.' 브리즈번 주는교회 주보입니다.'" :noindex="true">

    <x-ui.page-header kicker="주보 · Bulletin" :title="$bulletin->title">
        {{ $date }} 주일 주보입니다.
    </x-ui.page-header>

    <section class="section-bulletin container-site pb-12 lg:pb-16">
        {{-- Safari on a phone draws nothing inside the frame - neither
             the PDF nor the object's own fallback - so the link is the
             way in there rather than a courtesy. It sits above the
             frame: below it, the site header, the page header and a
             75vh frame put it off the first screen of every phone, and
             the reader was left looking at an empty white box with no
             way forward on it. --}}
        <div class="mb-6 flex flex-wrap items-center gap-5">
            <x-ui.button href="{{ $bulletin->pdfUrl() }}" target="_blank" rel="noopener">PDF 열기<span class="sr-only"> (새 창)</span></x-ui.button>
            <a href="{{ route('downloads') }}" class="text-caption font-bold text-accent hover:text-accent-700">← 자료실로 돌아가기</a>
        </div>

        <div class="overflow-hidden rounded-frame border-2 border-navy bg-paper">
            <object data="{{ $bulletin->pdfUrl() }}" type="application/pdf" class="block h-[75vh] w-full" aria-label="{{ $bulletin->title }} PDF">
                <p class="p-8 font-kr text-body-sm text-navy-400">이 브라우저에서는 주보를 바로 볼 수 없습니다. 위 버튼으로 열어 주세요.</p>
            </object>
        </div>
    </section>

</x-layout.app>
