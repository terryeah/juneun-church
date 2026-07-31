@props([
    'href',
    'active' => false,
    'mobile' => false,
])

{{-- Navigation link with the design's hover fill and active state. --}}
@php
    $base = $mobile
        ? 'block rounded-nav px-4 py-3.5 text-body font-medium font-kr'
        : 'rounded-nav px-3 py-[0.5625rem] text-body-sm font-medium font-kr';
    $state = $active
        ? 'bg-accent text-on-accent'
        : 'text-navy hover:bg-accent-100';
@endphp

<a href="{{ $href }}" @if ($active) aria-current="page" @endif {{ $attributes->merge(['class' => "$base $state"]) }}>{{ $slot }}</a>
