@props([
    'tag' => 'p',
    'color' => 'text-accent',
    'tracking' => 'tracking-[0.16em]',
])

{{-- Uppercase accent eyebrow label. --}}
<{{ $tag }} {{ $attributes->merge(['class' => "font-sans text-kicker font-extrabold uppercase {$tracking} {$color}"]) }}>{{ $slot }}</{{ $tag }}>
