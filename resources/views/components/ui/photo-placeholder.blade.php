@props([
    'label' => 'Photo',
    'background' => 'bg-navy/8',
    'color' => 'text-navy-400',
])

{{-- Drop-zone style placeholder used until real photography is supplied.

     The paper-page colours are props rather than defaults merged into
     `class`, the way x-ui.kicker takes its own: a caller passing
     `bg-navy-700/60` through `class` lands both backgrounds on the one
     element, and which of them wins is decided by their order in the
     built stylesheet rather than by the caller. --}}
<div {{ $attributes->merge(['class' => "flex items-center justify-center rounded-media {$background} {$color}"]) }}>
    <x-ui.kicker tag="span" color="">{{ $label }}</x-ui.kicker>
</div>
