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
        return view('pages.location');
    }
}
