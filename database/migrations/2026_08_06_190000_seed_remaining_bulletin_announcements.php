<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Adds the three 교회 소식 items from the 2 August bulletin that were
 * first recorded only as 교회 행사.
 *
 * They read as same-day notices, so they were filed as events on the
 * assumption that a dated announcement goes stale. The church wants
 * everything the bulletin prints under 교회 소식 to appear there as
 * well, with the ones that are gatherings also kept on the events
 * page - so these three now exist in both places.
 */
return new class extends Migration
{
    /**
     * The Sunday these notices were printed for.
     */
    protected const BULLETIN_DATE = '2026-08-02';

    /**
     * Titles seeded here, used to reverse the insert.
     *
     * @var list<string>
     */
    protected const TITLES = ['성찬이 있는 예배', '청년부 수련회', '2/4분기 정기 제직회'];

    /**
     * Insert each notice unless its title is already recorded.
     */
    public function up(): void
    {
        $bodies = [
            '성찬이 있는 예배' => '8월 첫 주일 예배는 성찬이 있는 예배로 드립니다.',
            '청년부 수련회' => '주일 예배 후 청년부 수련회가 있습니다. 부어주시는 은혜를 충만히 누리는 시간이 되도록 기도해 주시기 바랍니다.',
            '2/4분기 정기 제직회' => '주일 예배 후 2/4분기 정기 제직회가 있습니다. 모든 제직은 필히 참석해 주시기 바랍니다.',
        ];

        $author = DB::table('users')->orderBy('id')->value('id');

        foreach (self::TITLES as $title) {
            if (DB::table('announcements')->where('title', $title)->exists()) {
                continue;
            }

            DB::table('announcements')->insert([
                'title' => $title,
                'slug' => $this->slug($title),
                'content' => '<p>'.$bodies[$title].'</p>',
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
     * Remove the seeded notices.
     */
    public function down(): void
    {
        DB::table('announcements')->whereIn('title', self::TITLES)->delete();
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
