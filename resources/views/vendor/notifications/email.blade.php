<x-mail::message>
{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level === 'error')
# @lang('Whoops!')
@else
# @lang('Hello!')
@endif
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php
    $color = match ($level) {
        'success', 'error' => $level,
        default => 'primary',
    };
?>
<x-mail::button :url="$actionUrl" :color="$color">
{{ $actionText }}
</x-mail::button>
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Salutation --}}
@if (! empty($salutation))
{{ $salutation }}
@endif

{{--
    Everything Laravel puts below the last line is gone: the "Regards,"
    sign-off and the paragraph repeating the button as a pasteable link.
    Both were in English on a Korean letter, and the address the
    paragraph spelled out was an admin URL that means nothing to anyone
    who cannot already open it. The church signs its own letters in its
    own words - the notification writes its closing line itself - and
    the footer below still carries the name.

    A notification that sets its own salutation keeps it.
--}}
</x-mail::message>
