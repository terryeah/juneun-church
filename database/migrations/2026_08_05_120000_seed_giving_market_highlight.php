<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Turns the home 하이라이트 section over from the 청년부 리트릿 to the
 * 주일학교 주는마켓, and opens the 주는마켓 album the poster will be
 * uploaded into.
 *
 * The stored photo filename is cleared rather than replaced: the
 * poster does not exist yet, and HomeController falls back to the
 * first photo of the album named by highlight_link_album, so the
 * section picks the poster up by itself once it lands.
 *
 * No date, time or venue is recorded - the church's post gave none,
 * and a wrong one would be worse than none.
 */
return new class extends Migration
{
    /**
     * The album the poster belongs in.
     */
    private const ALBUM_SLUG = 'giving-market';

    /**
     * The 주는마켓 copy this migration installs.
     *
     * @var array<string, string>
     */
    private const HIGHLIGHT = [
        'highlight_photo' => '',
        'highlight_title' => "받는 기쁨보다 큰 기쁨을 배우는\n주일학교 주는마켓",
        'highlight_body' => '아이들이 나누는 기쁨을 배우는 주는마켓을 진행합니다. 받는 기쁨보다 주는 기쁨이 더 크다는 것을 직접 경험하는 시간입니다. 깨끗하고 사용할 수 있는 장난감과 그림책, 퍼즐과 보드게임, 인형과 블록을 가져와 주세요. 바자회나 물물교환이 아니라, 서로에게 기쁘게 내어 주는 자리입니다.',
        'highlight_link_album' => self::ALBUM_SLUG,
        'highlight_stat1_value' => '장난감 · 그림책 · 보드게임',
        'highlight_stat1_label' => '가져올 수 있는 것',
        'highlight_stat2_value' => '깨끗하고 사용할 수 있는 것',
        'highlight_stat2_label' => '나눔의 약속',
    ];

    /**
     * The 청년부 리트릿 copy this replaces, restored on rollback.
     *
     * @var array<string, string>
     */
    private const PREVIOUS_HIGHLIGHT = [
        'highlight_photo' => 'DbBBx0Dk31O-1.webp',
        'highlight_title' => "함께 모여 서로를 알아가는\n청년부 리트릿",
        'highlight_body' => '혼자서는 잘 살아가는 것 같지만, 사실 우리는 누군가의 사랑과 도움이 있어야 살아갑니다. 8월 2일 주일, 예배를 마치고 레크와 식사 그리고 예배까지 - 청년부가 함께 모여 교제하는 시간을 갖습니다.',
        'highlight_link_album' => 'youth-retreat-2026',
        'highlight_stat1_value' => '8월 2일 (주일)',
        'highlight_stat1_label' => '예배 후 시작',
        'highlight_stat2_value' => '레크 · 식사 · 예배',
        'highlight_stat2_label' => '함께하는 순서',
    ];

    /**
     * Open the album and point the highlight at it.
     */
    public function up(): void
    {
        $this->ensureAlbum();
        $this->writeHighlight(self::HIGHLIGHT);
    }

    /**
     * Put the 청년부 리트릿 highlight back. The album is only removed
     * while it is still empty, so a poster uploaded in the meantime is
     * never destroyed by a rollback.
     */
    public function down(): void
    {
        $this->writeHighlight(self::PREVIOUS_HIGHLIGHT);

        $albumId = DB::table('albums')->where('slug', self::ALBUM_SLUG)->value('id');

        if ($albumId !== null && ! DB::table('photos')->where('album_id', $albumId)->exists()) {
            DB::table('albums')->where('id', $albumId)->delete();
        }
    }

    /**
     * Create the 주는마켓 album unless it is already there.
     */
    private function ensureAlbum(): void
    {
        if (DB::table('albums')->where('slug', self::ALBUM_SLUG)->exists()) {
            return;
        }

        DB::table('albums')->insert([
            'title' => '주는마켓',
            'slug' => self::ALBUM_SLUG,
            'description' => '아이들이 주는 기쁨을 배우는 주일학교 주는마켓입니다.',
            'event_date' => null,
            'cover_photo_path' => null,
            'is_published' => true,
            'created_by' => DB::table('users')->orderBy('id')->value('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Write the given highlight settings, adding any key the settings
     * table does not carry yet.
     *
     * @param  array<string, string>  $settings
     */
    private function writeHighlight(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $updated = DB::table('site_settings')->where('key', $key)->update([
                'value' => $value,
                'updated_at' => now(),
            ]);

            if ($updated === 0) {
                DB::table('site_settings')->insert([
                    'key' => $key,
                    'value' => $value,
                    'group' => 'home',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        /** The settings collection is cached forever, and a raw write skips the model hook. */
        Cache::forget('site_settings');
    }
};
