<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Brings the stored service times in line with the timetable the church
 * published in August 2026.
 *
 * Three corrections and one addition: the 1부 예배 is the 사역자 예배 and
 * not the 봉사자 예배, the afternoon 주일예배 is named as the 2부 예배 for
 * the 청장년부, the single 주일학교 row splits into 유초등부 in the Hall and
 * 청소년부 in the Chapel, and each 본당 service now records the room it
 * meets in. The 수요기도회 was already correct and is left alone.
 *
 * The site keeps 본당 for the main site: the printed bulletin and every
 * existing setting say 본당, even though the Instagram poster wrote 본관.
 */
return new class extends Migration
{
    /**
     * The August 2026 timetable.
     *
     * @var array<string, string>
     */
    private const TIMETABLE = [
        'sunday_first_service_name' => '주일 1부 예배 (사역자 예배)',
        'sunday_first_service_time' => '주일 오전 10:30',
        'sunday_first_service_venue' => '교육관',
        'sunday_service_name' => '주일 2부 예배 (청장년부)',
        'sunday_service_time' => '주일 오후 1:30',
        'sunday_service_venue' => '본당 · Worship Centre',
        'kids_service_name' => '유초등부',
        'kids_service_time' => '주일 오후 1:30',
        'kids_service_venue' => '본당 · Hall',
        'youth_service_name' => '청소년부',
        'youth_service_time' => '주일 오후 1:30',
        'youth_service_venue' => '본당 · Chapel',
    ];

    /**
     * The timetable this replaces, read out of the live settings table
     * and restored verbatim on rollback.
     *
     * @var array<string, string>
     */
    private const PREVIOUS_TIMETABLE = [
        'sunday_first_service_name' => '주일 1부 예배 (봉사자 예배)',
        'sunday_first_service_time' => '주일 오전 10:30',
        'sunday_first_service_venue' => '교육관',
        'sunday_service_name' => '주일예배',
        'sunday_service_time' => '주일 오후 1:30',
        'sunday_service_venue' => '본당',
        'kids_service_name' => '주일학교',
        'kids_service_time' => '주일 오후 1:30',
        'kids_service_venue' => '본당',
    ];

    /**
     * The keys this migration introduces, removed again on rollback.
     *
     * @var list<string>
     */
    private const ADDED_KEYS = [
        'youth_service_name',
        'youth_service_time',
        'youth_service_venue',
    ];

    /**
     * Install the new timetable.
     */
    public function up(): void
    {
        $this->writeSettings(self::TIMETABLE);
    }

    /**
     * Put the previous timetable back and drop the 청소년부 keys, which
     * did not exist before this migration.
     */
    public function down(): void
    {
        DB::table('site_settings')->whereIn('key', self::ADDED_KEYS)->delete();

        $this->writeSettings(self::PREVIOUS_TIMETABLE);
    }

    /**
     * Write the given service settings, adding any key the settings
     * table does not carry yet.
     *
     * @param  array<string, string>  $settings
     */
    private function writeSettings(array $settings): void
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
                    'group' => 'service_times',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        /** The settings collection is cached forever, and a raw write skips the model hook. */
        Cache::forget('site_settings');
    }
};
