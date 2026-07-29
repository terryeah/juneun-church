@props([
    'title' => null,
    'description' => null,
])

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>
    @if ($description)
        <meta name="description" content="{{ $description }}">
    @endif
    <meta property="og:title" content="{{ $title ?? config('app.name') }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <link rel="preload" href="/fonts/GmarketSansMedium.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/GmarketSansBold.woff2" as="font" type="font/woff2" crossorigin>
    @vite(['resources/css/app.css', 'resources/ts/app.ts'])
    @if (config('services.cloudflare.web_analytics_token'))
        <script defer src="https://static.cloudflareinsights.com/beacon.min.js" data-cf-beacon='{"token": "{{ config('services.cloudflare.web_analytics_token') }}"}'></script>
    @endif
</head>
<body class="flex min-h-screen flex-col bg-paper font-sans text-navy antialiased">
    <x-layout.header />

    <main class="flex-1">
        {{ $slot }}
    </main>

    <x-layout.footer />
</body>
</html>
