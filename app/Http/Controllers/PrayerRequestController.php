<?php

namespace App\Http\Controllers;

use App\Models\PrayerRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * 기도제목: a 성도 shares one with their 셀, with every 성도, or with
 * the 목회자 alone, and the page shows each reader only what was
 * shared with them.
 *
 * 성도 전용 as a whole. No panel screen lists these - a request meant
 * for a 셀 stays in the 셀, and one meant for the 목회자 is read on
 * this page by the 목회자 and by nobody else.
 */
class PrayerRequestController extends Controller
{
    /**
     * The page: the sharing form, then one tab per audience.
     *
     * A 목회자 sees every 셀's requests and the ones addressed to them;
     * anybody else sees their own 셀 and the church-wide board.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        if (! $user?->isChurchMember()) {
            return $this->signInPage();
        }

        $tabs = ['cell' => '우리 셀', 'church' => '모든 성도', 'mine' => '내 기도제목'];

        if ($user->isPastor()) {
            $tabs = ['cell' => '셀별', 'church' => '모든 성도', 'pastor' => '목회자에게', 'mine' => '내 기도제목'];
        }

        $tab = array_key_exists($request->query('tab'), $tabs) ? $request->query('tab') : array_key_first($tabs);
        $cellId = $user->member->cell_id;

        $requests = PrayerRequest::query()
            ->with('user.member.cell.leader')
            ->latest()
            ->when($tab === 'cell' && $user->isPastor(), fn ($query) => $query->where('scope', 'cell'))
            ->when($tab === 'cell' && ! $user->isPastor(), fn ($query) => $cellId
                ? $query->forCell($cellId)
                : $query->whereRaw('1 = 0'))
            ->when($tab === 'church', fn ($query) => $query->where('scope', 'church'))
            ->when($tab === 'pastor', fn ($query) => $query->where('scope', 'pastor'))
            ->when($tab === 'mine', fn ($query) => $query->where('user_id', $user->getKey()))
            ->paginate(20)
            ->withQueryString();

        return view('pages.prayer.index', compact('tabs', 'tab', 'requests', 'cellId'));
    }

    /**
     * Share a 기도제목.
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isChurchMember(), 403);

        $data = $request->validate([
            'scope' => ['required', 'in:'.implode(',', array_keys(PrayerRequest::SCOPES))],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        if ($data['scope'] === 'cell' && ! $request->user()->member->cell_id) {
            return back()->withInput()->withErrors(['scope' => '아직 셀에 속해 있지 않아 셀에 나눌 수 없습니다.']);
        }

        $request->user()->prayerRequests()->create($data);

        return redirect()->route('prayer.index', ['tab' => $data['scope'] === 'pastor' ? 'mine' : $data['scope']])
            ->with('status', '기도제목을 나누었습니다.');
    }

    /**
     * Mark the writer's own request answered, or not after all.
     */
    public function answered(Request $request, PrayerRequest $prayerRequest): RedirectResponse
    {
        abort_unless($prayerRequest->user_id === $request->user()->getKey(), 403);

        $prayerRequest->update(['is_answered' => ! $prayerRequest->is_answered]);

        return back()->with('status', $prayerRequest->is_answered ? '응답받은 기도제목으로 표시했습니다.' : '응답 표시를 지웠습니다.');
    }

    /**
     * Take a request down: the writer may, and a 목회자 may.
     */
    public function destroy(Request $request, PrayerRequest $prayerRequest): RedirectResponse
    {
        abort_unless($prayerRequest->canBeRemovedBy($request->user()), 403);

        $prayerRequest->delete();

        return back()->with('status', '기도제목을 지웠습니다.');
    }

    /**
     * The sign-in notice standing in for the section.
     */
    private function signInPage(): View
    {
        return view('pages.members-only', [
            'kicker' => '함께 기도 · Prayer',
            'title' => '기도제목',
        ]);
    }
}
