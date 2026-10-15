<?php

namespace App\Http\Controllers;

use App\Models\Devotion;
use App\Models\DevotionNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * 큐티 (QT): the day's passage and guide, and a 묵상 each 성도 keeps
 * like a diary.
 *
 * 성도 전용 as a whole, like 교회 소식. A 묵상 is read by its writer
 * alone: every query below is narrowed to the signed-in account, and no
 * panel screen lists them.
 */
class DevotionController extends Controller
{
    /**
     * Today's 큐티 on top, the days before it underneath, each marked
     * when the reader has already written on it.
     */
    public function index(): View
    {
        if (! Auth::user()?->isChurchMember()) {
            return $this->signInPage();
        }

        $devotions = Devotion::query()->visible()->latest('devotion_date')->paginate(14);

        $written = DevotionNote::query()
            ->where('user_id', Auth::id())
            ->whereIn('devotion_id', $devotions->pluck('id'))
            ->pluck('devotion_id')
            ->all();

        return view('pages.qt.index', compact('devotions', 'written'));
    }

    /**
     * One day's 큐티 with the reader's own 묵상 under it.
     */
    public function show(Devotion $devotion): View
    {
        if (! Auth::user()?->isChurchMember()) {
            return $this->signInPage();
        }

        abort_unless(Devotion::query()->visible()->whereKey($devotion->getKey())->exists(), 404);

        $note = $devotion->notes()->where('user_id', Auth::id())->first();

        $previous = Devotion::query()->visible()
            ->whereDate('devotion_date', '<', $devotion->devotion_date)
            ->latest('devotion_date')
            ->first();

        $next = Devotion::query()->visible()
            ->whereDate('devotion_date', '>', $devotion->devotion_date)
            ->oldest('devotion_date')
            ->first();

        return view('pages.qt.show', compact('devotion', 'note', 'previous', 'next'));
    }

    /**
     * Keep the reader's 묵상 on a day. Emptying the box removes it.
     */
    public function save(Request $request, Devotion $devotion): RedirectResponse
    {
        abort_unless($request->user()->isChurchMember(), 403);
        abort_unless(Devotion::query()->visible()->whereKey($devotion->getKey())->exists(), 404);

        $body = trim((string) $request->validate([
            'body' => ['nullable', 'string', 'max:20000'],
        ])['body']);

        $match = ['devotion_id' => $devotion->getKey(), 'user_id' => $request->user()->getKey()];

        if ($body === '') {
            DevotionNote::query()->where($match)->delete();

            return redirect()->route('qt.show', $devotion)->with('status', '묵상을 지웠습니다.');
        }

        DevotionNote::query()->updateOrCreate($match, ['body' => $body]);

        return redirect()->route('qt.show', $devotion)->with('status', '묵상을 저장했습니다.');
    }

    /**
     * The reader's 묵상 노트: everything they have written, newest day first.
     */
    public function notes(): View
    {
        if (! Auth::user()?->isChurchMember()) {
            return $this->signInPage();
        }

        $notes = DevotionNote::query()
            ->where('user_id', Auth::id())
            ->with('devotion')
            ->join('devotions', 'devotions.id', '=', 'devotion_notes.devotion_id')
            ->orderByDesc('devotions.devotion_date')
            ->select('devotion_notes.*')
            ->paginate(10);

        return view('pages.qt.notes', compact('notes'));
    }

    /**
     * The sign-in notice standing in for the section.
     */
    private function signInPage(): View
    {
        return view('pages.members-only', [
            'kicker' => '말씀과 함께 · QT',
            'title' => '큐티',
        ]);
    }
}
