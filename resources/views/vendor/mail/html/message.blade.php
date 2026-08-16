<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
{{ config('app.name') }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{--
    No footer. Laravel signs every letter off with a copyright line in
    part-English, under a signature the church did not write either.
    The header already carries the name, linked to the site, which is
    all a reader needs to know who is writing.
--}}
</x-mail::layout>
