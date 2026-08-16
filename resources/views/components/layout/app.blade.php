@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'noindex' => false,
])

@php
    use App\Models\SiteSetting;

    /**
     * Split the single-line 본당 address setting into the parts schema.org
     * expects. "71 Newnham Rd, Mt Gravatt East QLD 4122" becomes a street,
     * a suburb, a state and a postcode; anything that does not match the
     * Australian pattern falls back to the whole string as the street so
     * the block never renders an empty or misleading address.
     */
    $addressMain = trim((string) SiteSetting::get('address_main'));
    $postalAddress = [
        '@type' => 'PostalAddress',
        'streetAddress' => $addressMain,
        'addressLocality' => 'Brisbane',
        'addressRegion' => 'QLD',
        'postalCode' => null,
        'addressCountry' => 'AU',
    ];

    if (preg_match('/^(.+?),\s*(.+?)\s+([A-Z]{2,3})\s+(\d{4})$/u', $addressMain, $addressParts)) {
        $postalAddress['streetAddress'] = $addressParts[1];
        $postalAddress['addressLocality'] = $addressParts[2];
        $postalAddress['addressRegion'] = $addressParts[3];
        $postalAddress['postalCode'] = $addressParts[4];
    }

    /** Only the dialable digits belong in schema.org, not the "(담임목사)" label. */
    $contactPhone = trim((string) SiteSetting::get('contact_phone'));
    $telephone = preg_match('/^([0-9+][0-9+\- ]*[0-9])/u', $contactPhone, $phoneParts)
        ? preg_replace('/[^0-9+]/', '', $phoneParts[1])
        : null;

    /** The social profiles Google uses to tie the site to its knowledge panel entry. */
    $sameAs = array_values(array_filter([
        SiteSetting::get('instagram_url'),
        SiteSetting::get('youtube_url'),
    ]));

    $structuredData = array_filter([
        '@'.'context' => 'https://schema.org',
        '@type' => 'Church',
        'name' => config('app.name'),
        'alternateName' => SiteSetting::get('church_name_en', 'Brisbane Juneun Church'),
        'url' => url('/'),
        'logo' => url('/favicon-512.png'),
        'address' => array_filter($postalAddress),
        'telephone' => $telephone,
        'email' => SiteSetting::get('contact_email'),
        'hasMap' => $addressMain !== ''
            ? 'https://www.google.com/maps?q='.urlencode($addressMain)
            : null,
        /**
         * The coordinates of 본당. Google matches a local church on
         * where it is far more readily than on a street string, and
         * this site names two addresses, so the point removes the
         * ambiguity about which one is the church.
         */
        'geo' => [
            '@type' => 'GeoCoordinates',
            'latitude' => -27.5308,
            'longitude' => 153.0838,
        ],
        'image' => url('/favicon-512.png'),
        'sameAs' => $sameAs,
    ]);

    /** One node, referenced rather than repeated, on every page. */
    $structuredData['@'.'id'] = url('/').'#church';

    /**
     * Social previews carry the church's name, which the browser tab
     * gets from the title but a shared card did not: a link to 예배
     * 안내 arrived in a chat reading only '예배 안내', with nothing to
     * say whose it was.
     */
    $shareTitle = $title ? $title.' · '.config('app.name') : config('app.name');
    $shareDescription = $description ?: '브리즈번 주는교회 - 함께 예배하고, 넉넉히 나누며, 예수 그리스도를 따라가는 젊은 한인교회입니다.';

    /**
     * Every page shares with a picture. Most pages pass none, and a
     * card with no image is the one nobody looks at twice - which
     * matters here because a link posted in a 단톡방 is how most people
     * arrive.
     */
    $shareImage = $image ?: asset('favicon-512.png');
@endphp

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}{{ $title ? ' · '.$title : '' }}</title>
    @if ($description)
        <meta name="description" content="{{ $description }}">
    @endif
    @if ($noindex)
        <meta name="robots" content="noindex, follow">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $shareTitle }}">
    <meta property="og:description" content="{{ $shareDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:locale" content="ko_KR">
    <meta property="og:image" content="{{ $shareImage }}">
    <meta property="og:image:alt" content="{{ $shareTitle }}">
    <meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $shareTitle }}">
    <meta name="twitter:description" content="{{ $shareDescription }}">
    <meta name="twitter:image" content="{{ $shareImage }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <meta name="theme-color" content="#16223c">
    <link rel="preconnect" href="https://media.juneun.com">
    {{-- Video stills come from YouTube's image host. Without this the
         first one on an album page pays a fresh DNS, TCP and TLS
         handshake from Brisbane before a single pixel arrives.

         No crossorigin: the stills are plain <img> tags, and a CORS
         preconnect warms a connection pool they never use. --}}
    <link rel="preconnect" href="https://i.ytimg.com">
    <link rel="preload" href="/fonts/GmarketSansMedium-modern.woff2" as="font" type="font/woff2" crossorigin>
    <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    @vite(['resources/css/app.css', 'resources/ts/app.ts'])
    {{-- The beacon is what Cloudflare counts, so an ignored address is
         simply not sent it. Safe to decide per request: this HTML is
         never cached at the edge - it answers no-cache, private, and
         Cloudflare reports it back as DYNAMIC. --}}
    @if (config('services.cloudflare.web_analytics_token') && ! in_array(request()->ip(), config('services.cloudflare.analytics_ignored_ips'), true))
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
