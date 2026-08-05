<?php

namespace App\Http\Controllers;

use App\Models\Offering;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Handles the giving page (헌금), including the weekly offering records
 * the bulletin publishes.
 */
class GivingController extends Controller
{
    /**
     * Display bank details plus the selected week's offering record.
     */
    public function __invoke(Request $request): View
    {
        $weeks = Offering::query()->orderByDesc('sunday_date')->limit(12)->get();

        /**
         * The sunday_date attribute is cast to a Carbon instance, so the
         * requested YYYY-MM-DD string is compared against its formatted
         * value. An unknown week falls back to the most recent one.
         */
        $requested = $request->query('week');

        $selected = $weeks->first(
            fn (Offering $offering): bool => $offering->sunday_date->toDateString() === $requested
        ) ?? $weeks->first();

        return view('pages.giving', [
            'weeks' => $weeks,
            'offering' => $selected,
        ]);
    }
}
