<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\View\View;

/**
 * Handles the serving members page (섬기는 사람들).
 */
class StaffController extends Controller
{
    /**
     * Display published members grouped by position in hierarchy order.
     */
    public function __invoke(): View
    {
        /** Positions in hierarchy order with their published members */
        $positions = Position::query()
            ->orderBy('sort_order')
            ->with(['staffMembers' => fn ($query) => $query->serving()])
            ->get()
            ->filter(fn (Position $position) => $position->staffMembers->isNotEmpty());

        return view('pages.people', compact('positions'));
    }
}
