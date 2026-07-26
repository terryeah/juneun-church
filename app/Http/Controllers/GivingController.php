<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Handles the online giving page (온라인헌금).
 */
class GivingController extends Controller
{
    /**
     * Display bank transfer details for online giving.
     */
    public function __invoke(): View
    {
        return view('pages.giving');
    }
}
