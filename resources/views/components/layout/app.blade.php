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
    @vite(['resources/css/app.css', 'resources/ts/app.ts'])
</head>
<body class="flex min-h-screen flex-col bg-cream font-sans text-navy antialiased">
    <x-layout.header />

    <main class="flex-1">
        {{ $slot }}
    </main>

    <x-layout.footer />
</body>
</html>
