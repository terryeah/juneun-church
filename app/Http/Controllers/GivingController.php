<?php

namespace App\Http\Controllers;

use App\Models\Offering;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Handles the online giving page (온라인 헌금), including the weekly
 * offering records the bulletin publishes.
 */
class GivingController extends Controller
{
    /**
     * Display bank details plus the selected week's offering record.
     */
    public function __invoke(Request $request): View
    {
        $weeks = Offering::query()->orderByDesc('sunday_date')->limit(12)->get();

        $selected = $weeks->firstWhere('sunday_date', $request->query('week'))
            ?? $weeks->first();

        return view('pages.giving', [
            'weeks' => $weeks,
            'offering' => $selected,
        ]);
    }
}
