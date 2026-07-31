@props([
    'title' => null,
    'description' => null,
])

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}{{ $title ? ' · '.$title : '' }}</title>
    @if ($description)
        <meta name="description" content="{{ $description }}">
    @endif
    <meta property="og:title" content="{{ $title ?? config('app.name') }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <meta name="theme-color" content="#16223c">
    <link rel="preconnect" href="https://media.juneun.com">
    <link rel="preload" href="/fonts/GmarketSansMedium-modern.woff2" as="font" type="font/woff2" crossorigin>
    <script type="application/ld+json">{!! json_encode([
        '@'.'context' => 'https://schema.org',
        '@type' => 'Church',
        'name' => config('app.name'),
        'alternateName' => 'Brisbane Juneun Church',
        'url' => url('/'),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => \App\Models\SiteSetting::get('address_main'),
            'addressLocality' => 'Brisbane',
            'addressRegion' => 'QLD',
            'addressCountry' => 'AU',
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @vite(['resources/css/app.css', 'resources/ts/app.ts'])
    @if (config('services.cloudflare.web_analytics_token'))
        <script defer src="https://static.cloudflareinsights.com/beacon.min.js" data-cf-beacon='{"token": "{{ config('services.cloudflare.web_analytics_token') }}"}'></script>
    @endif
</head>
<body class="flex min-h-screen flex-col bg-paper font-sans text-navy antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[60] focus:rounded-nav focus:bg-navy focus:px-4 focus:py-2 focus:text-cream">본문 바로가기</a>
    <x-layout.header />

    <main id="main" class="flex-1">
        {{ $slot }}
    </main>

    <x-layout.footer />
</body>
</html>
