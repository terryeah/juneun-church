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
        $tab = array_key_exists((string) $request->query('type'), self::TABS)
            ? (string) $request->query('type')
            : 'bulletins';

        $files = $tab === 'bulletins'
            ? Bulletin::query()->visible()->orderByDesc('published_at')->get()
            : Document::query()->visible()->orderByDesc('published_at')->get();

        /**
         * A guest is told whether anything is being held back, so the
         * page only offers to sign them in when there is something
         * behind the login rather than on every visit.
         */
        $hasRestricted = Auth::guest() && (
            Bulletin::query()->where('is_members_only', true)->exists()
            || Document::query()->where('is_members_only', true)->exists()
        );

        return view('pages.downloads', [
            'tabs' => self::TABS,
            'tab' => $tab,
            'files' => $files,
            'hasRestricted' => $hasRestricted,
        ]);
    }
}
