<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Records the printed bulletin of 2 August 2026 (주보 제2026-31호) as
 * data: the previous Sunday's offering, the church notices worth
 * keeping after the service, that Sunday's events, and the roster
 * names the bulletin introduces (예배위원, 셀장, 새가족 수료자).
 *
 * Every step checks for what is already there, so re-running the
 * migration on a database that has been edited by hand is safe.
 */
return new class extends Migration
{
    /**
     * The Sunday this bulletin was printed for.
     */
    private const BULLETIN_DATE = '2026-08-02';

    /**
     * Announcement titles seeded here, used to reverse the insert.
     *
     * @var list<string>
     */
    private const ANNOUNCEMENT_TITLES = [
        '8월 예배 장소 임시 변경 안내',
        '새가족 수료식',
        '예배실 이용 안내',
    ];

    /**
     * Event titles seeded here, used to reverse the insert.
     *
     * @var list<string>
     */
    private const EVENT_TITLES = ['청년부 리트릿', '2/4분기 정기 제직회', '성찬 예배'];

    /**
     * Roster names taken from the 8월 예배위원 table and the 셀 listing,
     * all serving as 집사.
     *
     * @var list<string>
     */
    private const DEACONS = ['백현무', '강희영', '김희성', '오성환', '최윤영', '진효선', '황희진'];

    /**
     * The 새가족 수료자 named in the bulletin, recorded as 성도.
     *
     * @var list<string>
     */
    private const NEW_FAMILY = ['유승희', '권미라', '권슬기'];

    /**
     * Seed the bulletin content, skipping anything already recorded.
     */
    public function up(): void
    {
        $author = DB::table('users')->orderBy('id')->value('id');

        $this->seedOffering($author);
        $this->seedAnnouncements($author);
        $this->seedEvents($author);
        $this->seedRoster();
    }

    /**
     * Remove everything this migration seeds. Roster names are only
     * deleted while they remain unpublished and unlinked to a login,
     * so a record since promoted to the public site or an account
     * survives the rollback.
     */
    public function down(): void
    {
        DB::table('members')
            ->whereIn('name', self::NEW_FAMILY)
            ->where('new_family_completed_at', self::BULLETIN_DATE)
            ->update(['new_family_completed_at' => null]);

        DB::table('cells')->where('name', '황희진 셀')->delete();

        DB::table('members')
            ->whereIn('name', array_merge(self::DEACONS, self::NEW_FAMILY))
            ->where('is_published', false)
            ->whereNull('user_id')
            ->delete();

        DB::table('events')->whereIn('title', self::EVENT_TITLES)->where('event_date', self::BULLETIN_DATE)->delete();
        DB::table('announcements')->whereIn('title', self::ANNOUNCEMENT_TITLES)->delete();
        DB::table('offerings')->where('sunday_date', '2026-07-26')->delete();
    }

    /**
     * The 지난 주 헌금 통계 table for Sunday 26 July 2026, totalling
     * 5,173.80 as printed.
     */
    private function seedOffering(?int $author): void
    {
        if (DB::table('offerings')->where('sunday_date', '2026-07-26')->exists()) {
            return;
        }

        DB::table('offerings')->insert([
            'sunday_date' => '2026-07-26',
            'items' => json_encode([
                ['category' => '주일헌금', 'name' => null, 'amount' => '840.10'],
                ['category' => '감사헌금', 'name' => null, 'amount' => '1855'],
                ['category' => '십일조', 'name' => null, 'amount' => '2228.7'],
                ['category' => '선교헌금', 'name' => '일본선교', 'amount' => '250'],
            ], JSON_UNESCAPED_UNICODE),
            'note' => null,
            'created_by' => $author,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * The 교회 소식 items that still apply after the service.
     */
    private function seedAnnouncements(?int $author): void
    {
        $announcements = [
            [
                'title' => self::ANNOUNCEMENT_TITLES[0],
                'content' => '<p>본 예배실 페인트 공사로 인해 8월 2일과 9일 예배 장소가 변경됩니다.</p>'
                    .'<p>예배 장소는 청소년부 예배실이며, 예배 시간은 오후 1시 30분으로 변경되지 않습니다.</p>'
                    .'<p>청소년부는 유초등부와 연합으로 예배합니다.</p>',
                'is_pinned' => true,
                'expires_at' => '2026-08-10 00:00:00',
            ],
            [
                'title' => self::ANNOUNCEMENT_TITLES[1],
                'content' => '<p>8월 2일 주일예배 중 새가족 수료식이 있었습니다.</p>'
                    .'<p>수료자는 유승희, 권미라, 권슬기 성도입니다.</p>'
                    .'<p>새가족을 환영하며 함께 기도해 주시기 바랍니다.</p>',
                'is_pinned' => false,
                'expires_at' => null,
            ],
            [
                'title' => self::ANNOUNCEMENT_TITLES[2],
                'content' => '<p>예배 혹은 모임 이후에는 머문 자리를 깨끗이 정돈해 주시기 바랍니다.</p>'
                    .'<p>예배실 안으로는 색이 있는 음료의 반입을 가급적 자제해 주시기 바랍니다.</p>',
                'is_pinned' => false,
                'expires_at' => null,
            ],
        ];

        foreach ($announcements as $announcement) {
            if (DB::table('announcements')->where('title', $announcement['title'])->exists()) {
                continue;
            }

            DB::table('announcements')->insert([
                'title' => $announcement['title'],
                'slug' => $this->slug($announcement['title']),
                'content' => $announcement['content'],
                'featured_image' => null,
                'is_published' => true,
                'is_pinned' => $announcement['is_pinned'],
                'published_at' => self::BULLETIN_DATE.' 13:30:00',
                'expires_at' => $announcement['expires_at'],
                'created_by' => $author,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * That Sunday's 교회 행사 entries.
     */
    private function seedEvents(?int $author): void
    {
        $events = [
            [
                'title' => self::EVENT_TITLES[0],
                'event_time' => '15:00:00',
                'location' => '교회',
                'description' => '예배 후 청년부 리트릿',
            ],
            [
                'title' => self::EVENT_TITLES[1],
                'event_time' => null,
                'location' => '본당',
                'description' => '모든 제직 필참',
            ],
            [
                'title' => self::EVENT_TITLES[2],
                'event_time' => '13:30:00',
                'location' => '청소년부 예배실',
                'description' => null,
            ],
        ];

        foreach ($events as $event) {
            $exists = DB::table('events')
                ->where('title', $event['title'])
                ->where('event_date', self::BULLETIN_DATE)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('events')->insert($event + [
                'event_date' => self::BULLETIN_DATE,
                'end_date' => null,
                'is_published' => true,
                'created_by' => $author,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * The roster names the bulletin introduces. They are entered
     * unpublished: the bulletin gives no photo or biography, and lay
     * positions never appear on the public 섬기는 사람들 page anyway.
     */
    private function seedRoster(): void
    {
        $deaconId = DB::table('positions')->where('name', '집사')->value('id');
        $memberId = DB::table('positions')->where('name', '성도')->value('id');

        foreach (self::DEACONS as $name) {
            $this->ensureMember($name, $deaconId);
        }

        foreach (self::NEW_FAMILY as $name) {
            $this->ensureMember($name, $memberId, self::BULLETIN_DATE);

            DB::table('members')->where('name', $name)->update(['new_family_completed_at' => self::BULLETIN_DATE]);
        }

        $leaderId = DB::table('members')->where('name', '황희진')->value('id');

        if ($leaderId !== null && ! DB::table('cells')->where('leader_id', $leaderId)->exists()) {
            DB::table('cells')->insert([
                'name' => '황희진 셀',
                'leader_id' => $leaderId,
                'description' => null,
                'sort_order' => (int) DB::table('cells')->max('sort_order') + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
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
     * Build a unique announcement slug the way the model does: Korean
     * titles slugify to nothing, so they fall back to a dated prefix.
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
