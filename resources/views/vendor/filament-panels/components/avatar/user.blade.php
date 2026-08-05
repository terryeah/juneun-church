{{--
    Override of filament-panels::components.avatar.user
    (vendor/filament/filament/resources/views/components/avatar/user.blade.php).

    The original always calls filament()->getUserAvatarUrl(), which falls
    back to Filament's UiAvatarsProvider and so sends a congregation
    member's name to ui-avatars.com on every admin page load, and draws
    a Korean name's 성 alone (양민규 as 양).

    Changed here: the photo is read straight off the account, and an
    account without one gets its initials as text in the same circle
    instead of a remote image. Everything else - the classes, the size
    handling, the alt text - is the original.

    This is the only view in Filament that resolves an avatar URL, so
    reconciling an upgrade means re-reading that one file.
--}}

@props([
    'user' => filament()->auth()->user(),
])

@php
    $src = $user instanceof \Filament\Models\Contracts\HasAvatar ? $user->getFilamentAvatarUrl() : null;
    $name = filament()->getUserName($user);
    $alt = __('filament-panels::layout.avatar.alt', ['name' => $name]);
    $size = $attributes->get('size', 'md');
    $initials = \App\Support\Initials::for($name);

    /**
     * A share of the circle's own width rather than a fixed size, so
     * two Korean syllables stay legible in the small topbar avatar and
     * grow with the larger one on the dashboard. Container query units
     * resolve against an ancestor container, never the element itself,
     * which is why the text sits in a span inside the circle. The
     * stylesheet carries a rem fallback for anything that cannot.
     */
    $fontSize = min(42, intdiv(84, max(mb_strlen($initials), 1)));
@endphp

@if (filled($src))
    <x-filament::avatar
        :src="$src"
        :alt="$alt"
        :attributes="
            \Filament\Support\prepare_inherited_attributes($attributes)
                ->class(['fi-user-avatar'])
        "
    />
@else
    <span
        title="{{ $name }}"
        aria-label="{{ $alt }}"
        {{
            \Filament\Support\prepare_inherited_attributes($attributes)
                ->except(['size', 'loading'])
                ->class([
                    'fi-avatar fi-circular fi-user-avatar fi-avatar-initials',
                    match ($size) {
                        'sm', 'md', 'lg' => "fi-size-{$size}",
                        default => $size,
                    },
                ])
        }}
    ><span @style(["font-size: {$fontSize}cqw"])>{{ $initials }}</span></span>
@endif
