<?php

namespace App\Http\Controllers;

use App\Models\BibleTyping;
use App\Models\BibleVerse;
use App\Models\Cell;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * 성경 타자: a 성도 types 개역한글 out verse by verse, and the page
 * keeps count for them, for their 셀, and for the whole church.
 *
 * 성도 전용 as a whole. A verse counts once it has been typed exactly,
 * spacing aside, and each verse counts once per person.
 */
class BibleTypingController extends Controller
{
    /**
     * The front page: where the reader is up to, and the leaderboards.
     */
    public function index(): View
    {
        if (! Auth::user()?->isChurchMember()) {
            return $this->signInPage();
        }

        $userId = Auth::id();
        $monthStart = now()->startOfMonth();

        $last = BibleTyping::query()->where('user_id', $userId)->latest('id')->with('verse')->first();

        $mine = [
            'total' => BibleTyping::query()->where('user_id', $userId)->count(),
            'month' => BibleTyping::query()->where('user_id', $userId)->where('created_at', '>=', $monthStart)->count(),
            'book' => $last?->verse->book ?? 1,
        ];

        $people = fn (bool $thisMonth) => BibleTyping::query()
            ->join('users', 'users.id', '=', 'bible_typings.user_id')
            ->when($thisMonth, fn ($query) => $query->where('bible_typings.created_at', '>=', $monthStart))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('verses')
            ->orderBy('users.name')
            ->limit(10)
            ->get(['users.id', 'users.name', DB::raw('count(*) as verses')]);

        $cellCounts = BibleTyping::query()
            ->join('members', 'members.user_id', '=', 'bible_typings.user_id')
            ->whereNotNull('members.cell_id')
            ->where('bible_typings.created_at', '>=', $monthStart)
            ->groupBy('members.cell_id')
            ->orderByDesc('verses')
            ->limit(10)
            ->get(['members.cell_id', DB::raw('count(*) as verses')])
            ->pluck('verses', 'cell_id');

        $cells = Cell::query()->with('leader')->findMany($cellCounts->keys())
            ->map(fn (Cell $cell) => ['name' => $cell->displayName(), 'verses' => $cellCounts[$cell->getKey()]])
            ->sortByDesc('verses')
            ->values();

        return view('pages.typing.index', [
            'mine' => $mine,
            'monthPeople' => $people(true),
            'allPeople' => $people(false),
            'cells' => $cells,
            'totalVerses' => BibleVerse::query()->count(),
        ]);
    }

    /**
     * The next verse of a book the reader has not typed yet.
     */
    public function write(Request $request): View
    {
        if (! Auth::user()?->isChurchMember()) {
            return $this->signInPage();
        }

        $book = (int) $request->query('book', 1);
        abort_unless(array_key_exists($book, BibleVerse::BOOKS), 404);

        $typed = BibleTyping::query()->where('user_id', Auth::id())->select('bible_verse_id');

        $verse = BibleVerse::query()
            ->where('book', $book)
            ->whereNotIn('id', $typed)
            ->orderBy('chapter')
            ->orderBy('verse')
            ->first();

        $progress = [
            'done' => BibleTyping::query()->where('user_id', Auth::id())
                ->whereHas('verse', fn ($query) => $query->where('book', $book))
                ->count(),
            'total' => BibleVerse::query()->where('book', $book)->count(),
        ];

        return view('pages.typing.write', compact('book', 'verse', 'progress'));
    }

    /**
     * Check what was typed against the verse and count it if it matches.
     */
    public function submit(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isChurchMember(), 403);

        $data = $request->validate([
            'verse_id' => ['required', 'integer', 'exists:bible_verses,id'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $verse = BibleVerse::query()->findOrFail($data['verse_id']);
        $next = redirect()->route('typing.write', ['book' => $verse->book]);

        if (! $verse->matches($data['body'])) {
            return $next->withInput()->withErrors(['body' => '말씀과 다른 곳이 있습니다. 한 글자씩 다시 맞춰 보세요.']);
        }

        BibleTyping::query()->firstOrCreate(['user_id' => $request->user()->getKey(), 'bible_verse_id' => $verse->getKey()]);

        return $next->with('status', $verse->reference().' 완료');
    }

    /**
     * The sign-in notice standing in for the section.
     */
    private function signInPage(): View
    {
        return view('pages.members-only', [
            'kicker' => '말씀 쓰기 · Bible Typing',
            'title' => '성경 타자',
        ]);
    }
}
