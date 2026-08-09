@props([
    'kicker',
    'title',
    'narrow' => false,
])

{{-- Interior page heading: accent eyebrow above a Korean display title.
     `narrow` drops the block into the same reading column the 교회 소식
     article uses, for pages built around a single card. The optional
     `badge` slot sits beside the title, for a page that is restricted
     as a whole rather than row by row. --}}
<header {{ $attributes->merge(['class' => 'container-site pt-12 pb-8 lg:pt-16 lg:pb-10']) }}>
    <div @class(['mx-auto max-w-3xl' => $narrow])>
        <x-ui.kicker>{{ $kicker }}</x-ui.kicker>
        <div class="mt-3 flex flex-wrap items-center gap-3">
            <h1 class="font-kr text-display-md font-medium">{{ $title }}</h1>
            @isset($badge)
                {{ $badge }}
            @endisset
        </div>
        @if ($slot->isNotEmpty())
            <p @class(['mt-4 text-body leading-relaxed text-navy-700', 'max-w-lg' => ! $narrow])>{{ $slot }}</p>
        @endif
    </div>
</header>
