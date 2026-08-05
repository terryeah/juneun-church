<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Moves the home 하이라이트 out of 사이트 설정 and into 교회 소식.
 *
 * The section used to be assembled from loose settings - a title, a
 * body, a photo filename, an album slug and two stat pairs. It now
 * renders whichever announcement carries is_highlighted, so those
 * settings have no reader left and are deleted.
 *
 * The 주는마켓 notice they currently hold is rewritten as a real
 * published announcement first, so the section keeps its content across
 * the deploy. The two stat pairs are dropped rather than carried over:
 * an announcement has nowhere to put them, and their wording is already
 * covered by the body copy.
 */
return new class extends Migration
{
    /**
     * Slug of the announcement this migration installs. Fixed rather
     * than derived from the run date so the migration can recognise its
     * own work and skip it; Korean titles slugify to nothing, so this
     * follows the model's news-YYYYMMDD fallback convention.
     */
    private const SLUG = 'news-20260806';

    /**
     * The 주는마켓 announcement, built from the settings it replaces.
     * The title's two display lines collapse into one sentence.
     *
     * @var array<string, string>
     */
    private const ANNOUNCEMENT = [
        'title' => '받는 기쁨보다 큰 기쁨을 배우는 주일학교 주는마켓',
        'content' => '<p>아이들이 나누는 기쁨을 배우는 주는마켓을 진행합니다. 받는 기쁨보다 주는 기쁨이 더 크다는 것을 직접 경험하는 시간입니다.</p><p>깨끗하고 사용할 수 있는 장난감과 그림책, 퍼즐과 보드게임, 인형과 블록을 가져와 주세요. 바자회나 물물교환이 아니라, 서로에게 기쁘게 내어 주는 자리입니다.</p>',
        'featured_image' => 'albums/giving-market/giving-market-1.webp',
    ];

    /**
     * The retired settings and the values they hold, restored verbatim
     * on rollback.
     *
     * @var array<string, string>
     */
    private const RETIRED_SETTINGS = [
        'highlight_photo' => '',
        'highlight_title' => "받는 기쁨보다 큰 기쁨을 배우는\n주일학교 주는마켓",
        'highlight_body' => '아이들이 나누는 기쁨을 배우는 주는마켓을 진행합니다. 받는 기쁨보다 주는 기쁨이 더 크다는 것을 직접 경험하는 시간입니다. 깨끗하고 사용할 수 있는 장난감과 그림책, 퍼즐과 보드게임, 인형과 블록을 가져와 주세요. 바자회나 물물교환이 아니라, 서로에게 기쁘게 내어 주는 자리입니다.',
        'highlight_link_album' => 'giving-market',
        'highlight_stat1_value' => '장난감 · 그림책 · 보드게임',
        'highlight_stat1_label' => '가져올 수 있는 것',
        'highlight_stat2_value' => '깨끗하고 사용할 수 있는 것',
        'highlight_stat2_label' => '나눔의 약속',
    ];

    /**
     * Publish the 주는마켓 announcement and drop the settings.
     */
    public function up(): void
    {
        if (! DB::table('announcements')->where('slug', self::SLUG)->exists()) {
            DB::table('announcements')->insert([
                ...self::ANNOUNCEMENT,
                'slug' => self::SLUG,
                'is_published' => true,
                'is_pinned' => false,
                'is_highlighted' => true,
                'published_at' => now(),
                'expires_at' => null,
                'created_by' => DB::table('users')->orderBy('id')->value('id'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('site_settings')->whereIn('key', array_keys(self::RETIRED_SETTINGS))->delete();

        $this->forgetSettingsCache();
    }

    /**
     * Put the settings back with the values they held, and remove the
     * announcement this migration created. An announcement that has
     * since been edited by hand is left alone - the slug match is only
     * trusted while the content is still untouched.
     */
    public function down(): void
    {
        DB::table('site_settings')->whereIn('key', array_keys(self::RETIRED_SETTINGS))->delete();

        DB::table('site_settings')->insert(collect(self::RETIRED_SETTINGS)
            ->map(fn (string $value, string $key): array => [
                'key' => $key,
                'value' => $value,
                'group' => 'home',
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->values()
            ->all());

        DB::table('announcements')
            ->where('slug', self::SLUG)
            ->where('title', self::ANNOUNCEMENT['title'])
            ->delete();

        $this->forgetSettingsCache();
    }

    /**
     * The settings collection is cached forever, and a raw write skips
     * the model hook that would clear it.
     */
    private function forgetSettingsCache(): void
    {
        Cache::forget('site_settings');
    }
};
