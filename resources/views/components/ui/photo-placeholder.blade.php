@props([
    'label' => 'Photo',
])

{{-- Drop-zone style placeholder used until real photography is supplied. --}}
<div {{ $attributes->merge(['class' => 'flex items-center justify-center rounded-media bg-navy/8 text-navy-400']) }}>
    <x-ui.kicker tag="span" color="">{{ $label }}</x-ui.kicker>
</div>
