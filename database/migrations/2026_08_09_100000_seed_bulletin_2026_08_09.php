<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Records the printed bulletin of 9 August 2026 (주보 제2026-32호) as
 * data: the previous Sunday's offering, the 교회 소식 column, the
 * gatherings it announces, the 7월 새가족 it introduces and the 전영주
 * 셀 it forms.
 *
 * Two notices were already carried by the 2 August bulletin and are
 * updated in place rather than repeated: the paint work notice, which
 * no longer names an end date, and the 주는마켓 highlight, which now
 * has one.
 *
 * Every step checks for what is already there, so re-running the
 * migration on a database that has been edited by hand is safe.
 */
return new class extends Migration
{
    /**
     * The Sunday this bulletin was printed for.
     */
    private const BULLETIN_DATE = '2026-08-09';

    /**
     * The Sunday the printed 지난 주 헌금 통계 covers.
     */
    private const OFFERING_DATE = '2026-08-02';

    /**
     * Announcement titles seeded here, used to reverse the insert.
     *
     * @var list<string>
     */
    private const ANNOUNCEMENT_TITLES = ['셀 배정', '6차 반찬나눔', '주일학교 교사 모집', '네이버 밴드 이전 안내'];

    /**
     * The paint work notice, carried over from the previous bulletin.
     */
    private const PAINT_NOTICE = '8월 예배 장소 임시 변경 안내';

    /**
     * The paint work notice as this bulletin prints it: the church is
     * still painting, and no end date is given any more.
     */
    private const PAINT_CONTENT = '<p>본 예배실 페인트 공사로 예배 장소가 변경됩니다.</p>'
        .'<p>예배 장소는 청소년부 예배실이며 예배 시간은 오후 1시 30분입니다.</p>'
        .'<p>청소년부는 유초등부와 연합으로 예배합니다.</p>';

    /**
     * The paint work notice as the 2 August bulletin printed it,
     * restored on rollback along with its original expiry.
     */
    private const PAINT_CONTENT_BEFORE = '<p>본 예배실 페인트 공사로 인해 8월 2일과 9일 예배 장소가 변경됩니다.</p>'
        .'<p>예배 장소는 청소년부 예배실이며, 예배 시간은 오후 1시 30분으로 변경되지 않습니다.</p>'
        .'<p>청소년부는 유초등부와 연합으로 예배합니다.</p>';

    /**
     * The 주는마켓 announcement carrying the home page highlight.
     */
    private const MARKET_SLUG = 'news-20260806';

    /**
     * The dates this bulletin adds to the 주는마켓 announcement, which
     * was published without any.
     */
    private const MARKET_DATES = '<p>일시는 9월 6일 주일이고, 물건은 8월 23일과 30일에 모읍니다.</p>';

    /**
     * Event titles and dates seeded here, used to reverse the insert.
     *
     * @var array<string, string>
     */
    private const EVENT_DATES = ['6차 반찬나눔' => '2026-08-29', '유초등부 주는 마켓' => '2026-09-06'];

    /**
     * The 7월 새가족 this bulletin introduces, recorded as 성도. The
     * three named alongside him were already recorded by the 2 August
     * bulletin. Their children are not entered: the bulletin names them
     * only in passing, and the roster carries no minors.
     *
     * @var list<string>
     */
    private const NEW_FAMILY = ['김용철'];

    /**
     * The 셀장 of the cell this bulletin forms.
     */
    private const CELL_LEADER = '전영주';

    /**
     * The members the bulletin assigns to the 전영주 셀.
     *
     * @var list<string>
     */
    private const CELL_MEMBERS = ['유승희', '권미라', '권슬기'];

    /**
     * Seed the bulletin content, skipping anything already recorded.
     */
    public function up(): void
    {
        $author = DB::table('users')->orderBy('id')->value('id');

        $this->seedOffering($author);
        $this->updateCarriedNotices();
        $this->seedAnnouncements($author);
        $this->seedEvents($author);
        $this->seedRoster();
    }

    /**
     * Remove everything this migration seeds and put the two carried
     * notices back as the previous bulletin printed them. Roster names
     * are only deleted while they remain unpublished and unlinked to a
     * login, so a record since promoted to the public site or an
     * account survives the rollback.
     */
    public function down(): void
    {
        DB::table('members')->whereIn('name', self::CELL_MEMBERS)->update(['cell_id' => null]);
        DB::table('cells')->where('name', self::CELL_LEADER.' 셀')->delete();

        DB::table('members')
            ->whereIn('name', array_merge(self::NEW_FAMILY, [self::CELL_LEADER]))
            ->where('is_published', false)
            ->whereNull('user_id')
            ->delete();

        foreach (self::EVENT_DATES as $title => $date) {
            DB::table('events')->where('title', $title)->where('event_date', $date)->delete();
        }

        DB::table('announcements')->whereIn('title', self::ANNOUNCEMENT_TITLES)->delete();

        DB::table('announcements')->where('title', self::PAINT_NOTICE)->update([
            'content' => self::PAINT_CONTENT_BEFORE,
            'expires_at' => '2026-08-10 00:00:00',
            'updated_at' => now(),
        ]);

        $market = DB::table('announcements')->where('slug', self::MARKET_SLUG)->first();

        if ($market !== null && str_contains($market->content, self::MARKET_DATES)) {
            DB::table('announcements')->where('slug', self::MARKET_SLUG)->update([
                'content' => str_replace(self::MARKET_DATES, '', $market->content),
                'updated_at' => now(),
            ]);
        }

        DB::table('offerings')->where('sunday_date', self::OFFERING_DATE)->delete();
    }

    /**
     * The 지난 주 헌금 통계 table for Sunday 2 August 2026, totalling
     * 4,178 as printed.
     */
    private function seedOffering(?int $author): void
    {
        if (DB::table('offerings')->where('sunday_date', self::OFFERING_DATE)->exists()) {
            return;
        }

        DB::table('offerings')->insert([
            'sunday_date' => self::OFFERING_DATE,
            'items' => json_encode([
                ['category' => '주일헌금', 'name' => null, 'amount' => '1066'],
                ['category' => '감사헌금', 'name' => null, 'amount' => '150'],
                ['category' => '십일조', 'name' => null, 'amount' => '2912'],
                ['category' => '선교헌금', 'name' => '일본선교', 'amount' => '50'],
            ], JSON_UNESCAPED_UNICODE),
            'note' => null,
            'created_by' => $author,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Bring the two notices this bulletin repeats up to date instead of
     * printing them twice: the paint work now runs without a stated end
     * date, and the 주는마켓 has finally been given its dates.
     */
    private function updateCarriedNotices(): void
    {
        DB::table('announcements')->where('title', self::PAINT_NOTICE)->update([
            'content' => self::PAINT_CONTENT,
            'is_pinned' => true,
            'expires_at' => '2026-08-31 00:00:00',
            'updated_at' => now(),
        ]);

        $market = DB::table('announcements')->where('slug', self::MARKET_SLUG)->first();

        if ($market !== null && ! str_contains($market->content, self::MARKET_DATES)) {
            DB::table('announcements')->where('slug', self::MARKET_SLUG)->update([
                'content' => $market->content.self::MARKET_DATES,
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * The 교회 소식 items this bulletin prints for the first time.
     */
    private function seedAnnouncements(?int $author): void
    {
        $bodies = [
            '셀 배정' => '<p>전영주 셀에 유승희, 권미라, 권슬기 성도가 배정되었습니다.</p>',
            '6차 반찬나눔' => '<p>지역 청년들을 위한 반찬 나눔 사역이 있습니다. 함께 봉사하기 원하시는 분은 오승희 집사(봉사부장)에게 신청해주시기 바랍니다.</p>'
                .'<p>8월 29일 토요일 오전 9시에 음식을 만들고, 오후 2시에 반찬을 배달합니다. 모든 일정은 당일 진행됩니다.</p>',
            '주일학교 교사 모집' => '<p>유초등부와 청소년부에서 교사를 모집합니다.</p>'
                .'<p>아이들을 사랑으로 품어주시며 함께 예배드릴 성도님께서는 각 부서 담당자에게 문의해주시기 바랍니다.</p>'
                .'<p>정교사는 세례교인 이상, 보조교사는 학습 이상입니다.</p>',
            '네이버 밴드 이전 안내' => '<p>기존 네이버 밴드의 해킹 문제로 새로운 밴드로 이전합니다.</p>'
                .'<p>새 밴드에 가입해주시기 바랍니다.</p>',
        ];

        foreach (self::ANNOUNCEMENT_TITLES as $title) {
            if (DB::table('announcements')->where('title', $title)->exists()) {
                continue;
            }

            DB::table('announcements')->insert([
                'title' => $title,
                'slug' => $this->slug($title),
                'content' => $bodies[$title],
                'is_published' => true,
                'is_pinned' => false,
                'published_at' => self::BULLETIN_DATE.' 13:30:00',
                'created_by' => $author,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * The gatherings the 교회 소식 column announces.
     */
    private function seedEvents(?int $author): void
    {
        $events = [
            [
                'title' => '6차 반찬나눔',
                'event_time' => '09:00:00',
                'location' => '교육관',
                'description' => '오전 9시 음식 만들기, 오후 2시 반찬 배달',
            ],
            [
                'title' => '유초등부 주는 마켓',
                'event_time' => null,
                'location' => '본당',
                'description' => '물건은 8월 23일과 30일에 모읍니다.',
            ],
        ];

        foreach ($events as $event) {
            $date = self::EVENT_DATES[$event['title']];

            if (DB::table('events')->where('title', $event['title'])->where('event_date', $date)->exists()) {
                continue;
            }

            DB::table('events')->insert($event + [
                'event_date' => $date,
                'end_date' => null,
                'is_published' => true,
                'created_by' => $author,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * The 새가족 and the 셀 the bulletin introduces. Names are entered
     * unpublished: the bulletin gives no photo or biography, and lay
     * positions never appear on the public 섬기는 사람들 page anyway.
     */
    private function seedRoster(): void
    {
        $memberId = DB::table('positions')->where('name', '성도')->value('id');

        foreach (self::NEW_FAMILY as $name) {
            $this->ensureMember($name, $memberId, self::OFFERING_DATE);
        }

        $this->ensureMember(self::CELL_LEADER, $memberId);

        $leaderId = DB::table('members')->where('name', self::CELL_LEADER)->value('id');

        if ($leaderId === null) {
            return;
        }

        if (! DB::table('cells')->where('leader_id', $leaderId)->exists()) {
            DB::table('cells')->insert([
                'name' => self::CELL_LEADER.' 셀',
                'leader_id' => $leaderId,
                'description' => null,
                'sort_order' => (int) DB::table('cells')->max('sort_order') + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $cellId = DB::table('cells')->where('leader_id', $leaderId)->value('id');

        DB::table('members')->whereIn('name', self::CELL_MEMBERS)->update(['cell_id' => $cellId]);
    }

    /**
     * Add a roster entry unless someone of that name is already on it.
     */
    private function ensureMember(string $name, ?int $positionId, ?string $newFamilyCompletedAt = null): void
    {
        if (DB::table('members')->where('name', $name)->exists()) {
            return;
        }

        DB::table('members')->insert([
            'name' => $name,
            'position_id' => $positionId,
            'status' => '재적',
            'new_family_completed_at' => $newFamilyCompletedAt,
            'sort_order' => 0,
            'is_published' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Build a unique slug the way the model does: Korean titles
     * slugify to nothing, so they fall back to a dated prefix.
     */
    private function slug(string $title): string
    {
        $base = Str::slug($title);

        if (mb_strlen($base) < 4) {
            $base = 'news-'.str_replace('-', '', self::BULLETIN_DATE);
        }

        $slug = $base;
        $suffix = 2;

        while (DB::table('announcements')->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
};
