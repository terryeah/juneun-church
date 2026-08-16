<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google_maps' => [
        'key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'cloudflare' => [
        'api_token' => env('CLOUDFLARE_API_TOKEN'),
        'zone_id' => env('CLOUDFLARE_ZONE_ID'),
        'account_id' => env('CLOUDFLARE_ACCOUNT_ID'),
        'rum_site_tag' => env('CLOUDFLARE_RUM_SITE_TAG'),
        'web_analytics_token' => env('CLOUDFLARE_WEB_ANALYTICS_TOKEN'),

        /**
         * Addresses whose page openings are not counted as visits.
         *
         * 방문자 통계 is meant to say how the congregation uses the site,
         * and the people building and running it look from the same
         * address every day. Cloudflare Web Analytics counts whoever
         * loads its beacon, so an address named here simply is not
         * served the beacon.
         *
         * The addresses live in the environment rather than in this
         * file because the repository is public, and a home address is
         * not ours to publish.
         */
        'analytics_ignored_ips' => array_values(array_filter(array_map(
            trim(...),
            explode(',', (string) env('ANALYTICS_IGNORED_IPS', '')),
        ))),
    ],

    /**
     * Umami, which counts the visits 방문자 통계 reports.
     *
     * Cloudflare's own figures came from a beacon Cloudflare injects at
     * the edge, which meant no address could be left out of them and no
     * page could be told from another. This script is ours, in our own
     * layout, so both are decided here.
     */
    'umami' => [
        'website_id' => env('UMAMI_WEBSITE_ID'),
        'script_url' => env('UMAMI_SCRIPT_URL', 'https://cloud.umami.is/script.js'),
        'api_url' => env('UMAMI_API_URL', 'https://api.umami.is/v1'),
        'api_key' => env('UMAMI_API_KEY'),
    ],

];
