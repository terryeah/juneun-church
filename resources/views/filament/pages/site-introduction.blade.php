<x-filament-panels::page>
    {{-- Framed rather than inlined: the walkthrough carries its own
         stylesheet, and letting it loose in the panel would have its
         section and body rules fight Filament's. --}}
    <div class="fi-site-introduction">
        <iframe
            src="{{ route('site-introduction') }}"
            title="홈페이지 소개"
            loading="lazy"
        ></iframe>

        <p>
            <a href="{{ route('site-introduction') }}" target="_blank" rel="noopener">
                새 창에서 열기 (발표용)
            </a>
        </p>
    </div>

    <style>
        .fi-site-introduction iframe {
            width: 100%;
            height: 75vh;
            min-height: 32rem;
            border: 0.0625rem solid var(--gray-200);
            border-radius: 0.75rem;
            background: #ffffff;
        }
        .dark .fi-site-introduction iframe { border-color: var(--gray-700); background: #0b1120; }
        .fi-site-introduction p { margin-block-start: 0.75rem; font-size: 0.875rem; }
        .fi-site-introduction a { color: var(--primary-600); font-weight: 600; }
        .dark .fi-site-introduction a { color: var(--primary-400); }
    </style>
</x-filament-panels::page>
