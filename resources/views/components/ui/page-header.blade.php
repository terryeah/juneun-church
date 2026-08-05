@props([
    'kicker',
    'title',
    'center' => false,
])

{{-- Interior page heading: accent eyebrow above a Korean display title.
     `center` stacks the block on the page centre the way the 교회 소식
     article heading does, for pages built around a single card. --}}
<header {{ $attributes->merge(['class' => 'container-site pt-12 pb-8 lg:pt-16 lg:pb-10']) }}>
    <div @class(['mx-auto max-w-xl text-center' => $center])>
        <x-ui.kicker>{{ $kicker }}</x-ui.kicker>
        <h1 class="mt-3 font-kr text-display-md font-medium">{{ $title }}</h1>
        @if ($slot->isNotEmpty())
            <p @class(['mt-4 text-body leading-relaxed text-navy-700', 'max-w-lg' => ! $center])>{{ $slot }}</p>
        @endif
    </div>
</header>
