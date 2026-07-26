@props([
    'href' => '#',
    'variant' => 'primary',
])

{{-- Primary (accent fill) and secondary (2px navy outline) buttons. --}}
@php
    $base = 'inline-flex items-center gap-2 rounded-btn px-5 py-3 text-[15px] font-extrabold';
    $styles = $variant === 'primary'
        ? 'bg-accent text-on-accent hover:bg-accent-700 active:bg-accent-700'
        : 'border-2 border-navy text-navy hover:bg-accent-100 active:bg-accent-100';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => "$base $styles"]) }}>{{ $slot }}</a>
