<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\View\View;

/**
 * Handles the directions page (오시는 길).
 */
class LocationController extends Controller
{
    /**
     * Display addresses, maps, service times and contact details.
     */
    public function __invoke(): View
    {
        /**
         * The pick-up notice graphic, taken as the first photo of its
         * album. Keying on the album rather than a filename means the
         * notice survives being replaced with a new upload.
         */
        $pickupPhoto = Album::query()
            ->where('slug', 'youth-pickup-notice')
            ->first()
            ?->photos()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        return view('pages.location', compact('pickupPhoto'));
    }
}
