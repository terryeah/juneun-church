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
 *
 * The page is 성도 전용 as a whole: the 주보 carries cell assignments,
 * rosters and the week's giving, and the forms are the church's own
 * paperwork.
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
        /** Asked before the files are fetched, so nothing held back is ever loaded. */
        if (! Auth::user()?->isChurchMember()) {
            return view('pages.members-only', [
                'kicker' => '교회 자료 · Downloads',
                'title' => '자료실',
                'body' => '주보와 교회 서식에는 셀 편성과 섬김이 명단처럼 성도의 정보가 담겨 있어 성도에게만 공개됩니다.',
            ]);
        }

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

        return view('pages.downloads', [
            'tabs' => self::TABS,
            'tab' => $tab,
            'files' => $files,
        ]);
    }
}
