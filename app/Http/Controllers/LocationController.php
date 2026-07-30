<?php

namespace App\Http\Controllers;

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
        /** The pick-up notice graphic from the church Instagram */
        $pickupPhoto = \App\Models\Photo::query()->where('filename', 'DYzJDV_EyAf-1.webp')->first();

        return view('pages.location', compact('pickupPhoto'));
    }
}
