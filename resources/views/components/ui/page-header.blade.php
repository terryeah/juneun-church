@props([
    'kicker',
    'title',
])

{{-- Interior page heading: accent eyebrow above a Korean display title. --}}
<header {{ $attributes->merge(['class' => 'container-site pt-12 pb-8 lg:pt-16 lg:pb-10']) }}>
    <x-ui.kicker>{{ $kicker }}</x-ui.kicker>
    <h1 class="mt-3 font-kr text-display-md font-medium">{{ $title }}</h1>
    @if ($slot->isNotEmpty())
        <p class="mt-4 max-w-lg text-body leading-relaxed text-navy-700">{{ $slot }}</p>
    @endif
</header>
