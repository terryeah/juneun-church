<?php

namespace App\Http\Controllers;

use App\Models\Bulletin;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Handles 자료실, which gathers the 주보 and the church's standing
 * documents behind one set of tabs.
 *
 * Each tab is a real URL, so the page works without JavaScript and the
 * swap in the browser is only an enhancement over a normal navigation.
 */
class DownloadsController extends Controller
{
    /**
     * The tabs, in the order they are offered.
     */
    private const TABS = [
        'bulletins' => '주보',
        'documents' => '문서',
    ];

    /**
     * Display the selected tab's files.
     */
    public function __invoke(Request $request): View
    {
        /**
         * The type is checked before it is used as one: ?type[]=x
         * arrives as an array, and casting an array to a string is a
         * PHP error rather than a value - an unauthenticated 500 on a
         * public page. 앨범 was fixed for this; 자료실 was not.
         */
        $type = $request->query('type');
        $tab = is_string($type) && array_key_exists($type, self::TABS) ? $type : 'bulletins';

        $files = $tab === 'bulletins'
            ? Bulletin::query()->visible()->orderByDesc('published_at')->get()
            : Document::query()->visible()->orderByDesc('published_at')->get();

        /**
         * Anyone who is not on the 교적 is told whether something is
         * being held back, so the page only offers to sign them in when
         * there is something behind it rather than on every visit. A
         * signed-in 일반회원 is in the same position as a guest here.
         */
        $hasRestricted = ! Auth::user()?->isChurchMember() && (
            Bulletin::query()->where('is_members_only', true)->exists()
            || Document::query()->where('is_members_only', true)->exists()
        );

        /** Whether the other tab has anything this reader may open. */
        $hasOtherTab = $tab === 'bulletins'
            ? Document::query()->visible()->exists()
            : Bulletin::query()->visible()->exists();

        return view('pages.downloads', [
            'tabs' => self::TABS,
            'hasOtherTab' => $hasOtherTab,
            'tab' => $tab,
            'files' => $files,
            'hasRestricted' => $hasRestricted,
        ]);
    }
}
