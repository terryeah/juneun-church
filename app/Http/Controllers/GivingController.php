<?php

namespace App\Http\Controllers;

use App\Models\Offering;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Handles the giving page (헌금), including the weekly offering records
 * the bulletin publishes.
 *
 * The page is 성도 전용 as a whole. The records name who gave what, so
 * they are only ever fetched for somebody on the 교적 - the page used
 * to load twelve weeks of them for a guest and then decline to draw
 * them, which put the names in memory for a reader who may not have
 * them at all.
 */
class GivingController extends Controller
{
    /**
     * Display bank details plus the selected week's offering record.
     */
    public function __invoke(Request $request): View
    {
        if (! Auth::user()?->isChurchMember()) {
            return view('pages.members-only', [
                'kicker' => '은혜를 흘려보내는 · Giving',
                'title' => '헌금',
                'body' => '주보에 실리는 주일 헌금 내역에는 헌금하신 성도의 이름이 담겨 있어 헌금 안내와 함께 성도에게만 공개됩니다.',
            ]);
        }

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
