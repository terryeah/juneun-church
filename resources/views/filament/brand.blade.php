{{--
    The panel's brand: the church mark followed by its name.

    Filament shows either a logo or the brand name, never both, so the
    pair is handed over as the logo. Filament wraps this in .fi-logo,
    which sets the type and the colour - near-black on the light theme,
    white on the dark one - and the mark inherits that colour rather
    than carrying one of its own, so it stays legible in either.

    The mark is sized from the panel's own stylesheet rather than by the
    utility classes it carries on the public site: the panel loads
    Filament's CSS, where those classes do not exist, and an unsized SVG
    either collapses to nothing or falls back to its default 300x150.
--}}
<x-ui.logo />
{{ config('app.name') }}
