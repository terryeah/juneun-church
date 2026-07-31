{{-- Site pagination: quiet navy text links, accent current page, no boxes. --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="페이지 이동" class="mt-10 flex items-center justify-center gap-1 font-kr text-body-sm">
        @if ($paginator->onFirstPage())
            <span class="px-3 py-2 text-navy-400" aria-disabled="true">이전</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="rounded-nav px-3 py-2 text-navy transition-colors hover:text-accent">이전</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 py-2 text-navy-400">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="px-3 py-2 font-extrabold text-accent" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="rounded-nav px-3 py-2 text-navy transition-colors hover:text-accent">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="rounded-nav px-3 py-2 text-navy transition-colors hover:text-accent">다음</a>
        @else
            <span class="px-3 py-2 text-navy-400" aria-disabled="true">다음</span>
        @endif
    </nav>
@endif
