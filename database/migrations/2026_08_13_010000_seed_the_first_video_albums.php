<?php

use App\Models\Album;
use App\Models\Video;
use Illuminate\Database\Migrations\Migration;

/**
 * Loads the church's existing videos, which were kept in a BAND post.
 *
 * Every one of them is unlisted on YouTube: viewable by anyone holding
 * the link and listed nowhere, and several say so in their own titles -
 * "주는교회 성도님들만 공유하는 영상입니다". Publishing an identifier on a
 * page a stranger can open is the same as publishing the video, so
 * these albums are 성도 전용 and stay that way.
 *
 * The grouping is not in the source, which is a flat list. It follows
 * the names the church gave the videos, which group themselves: three
 * 청소년부 trips, five 반찬 나눔 in order, four 유초등부 성경학교. The
 * office can regroup them by changing an album in the panel.
 *
 * Dates come from the videos' own YouTube titles where they state one.
 * The albums with none are left blank rather than guessed at.
 */
return new class extends Migration
{
    /**
     * The albums, and the videos in each.
     *
     * The slug is stated rather than derived: Str::slug transliterates,
     * and a Korean title transliterates to nothing at all, so deriving
     * one would give every album the same empty slug - and, since the
     * albums are matched on it, put all sixteen videos in the first.
     *
     * @var array<string, array{slug: string, date: ?string, videos: array<string, string>}>
     */
    private const ALBUMS = [
        '청소년부' => [
            'slug' => 'video-youth',
            'date' => '2024-06-24',
            'videos' => [
                '556vWaIbHSE' => '겨울 리트릿',
                'MU_cM9z_QDU' => '미니 리트릿',
                'qBQCrB0cLPA' => '2024 여름 수련회',
            ],
        ],
        '청년부' => [
            'slug' => 'video-young-adults',
            'date' => '2024-01-06',
            'videos' => [
                '1_9f57Inll0' => '학생·청년부 간친회',
                '7jV3hZ_b1mw' => '2024 수련회',
            ],
        ],
        '유초등부' => [
            'slug' => 'video-children',
            'date' => '2026-07-12',
            'videos' => [
                'MHIn7tuo2Gc' => '2024 겨울 성경학교',
                'CqKtyS7whu0' => '2025 여름 성경학교',
                'n9vEpLzepEg' => '2025 봄 성경학교',
                '2JV7iOgjw5Q' => '성경암송대회',
            ],
        ],
        '반찬 나눔' => [
            'slug' => 'video-banchan',
            'date' => null,
            'videos' => [
                'QAOKqdXICjo' => '첫 반찬 나눔',
                'rbjyTVAh16k' => '2차 반찬 나눔',
                '_RP51iRbnKA' => '3차 반찬 나눔',
                'Eo2cfZDAHK4' => '4차 반찬 나눔',
                'Mfiy_yNCYq0' => '5차 반찬 나눔',
            ],
        ],
        '교회 행사' => [
            'slug' => 'video-church-events',
            'date' => '2024-11-02',
            'videos' => [
                'RVMufc4p0Xo' => '첫 성탄 예배 바베큐',
                '5EFmBzwa7_A' => '시니어 야유회',
            ],
        ],
    ];

    /**
     * Create the albums and their videos, skipping anything already in.
     */
    public function up(): void
    {
        foreach (self::ALBUMS as $title => $album) {
            $record = Album::query()->firstOrCreate(
                ['slug' => $album['slug']],
                [
                    'title' => $title,
                    'type' => Album::TYPE_VIDEO,
                    'event_date' => $album['date'],
                    'is_published' => true,
                    'is_members_only' => true,
                ],
            );

            $order = 0;

            foreach ($album['videos'] as $youtubeId => $videoTitle) {
                $order++;

                Video::query()->firstOrCreate(
                    ['youtube_id' => $youtubeId],
                    [
                        'album_id' => $record->getKey(),
                        'title' => $videoTitle,
                        'sort_order' => $order,
                    ],
                );
            }
        }
    }

    /**
     * Remove them again.
     */
    public function down(): void
    {
        foreach (self::ALBUMS as $album) {
            Video::query()->whereIn('youtube_id', array_keys($album['videos']))->delete();
        }

        Album::query()->ofType(Album::TYPE_VIDEO)->where('slug', 'like', 'video-%')->delete();
    }
};
