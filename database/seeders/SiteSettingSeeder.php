<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

/**
 * Seeds the default site settings.
 *
 * Values mirror the design handoff and remain editable in the admin
 * panel; nothing on the public site hard-codes these details.
 */
class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'church_name', 'value' => '브리즈번 주는교회', 'group' => 'contact'],
            ['key' => 'church_name_en', 'value' => 'Brisbane Juneun Church', 'group' => 'contact'],
            ['key' => 'denomination', 'value' => '대한예수교장로회', 'group' => 'contact'],
            ['key' => 'address_main_label', 'value' => '본당', 'group' => 'contact'],
            ['key' => 'address_main', 'value' => '71 Newnham Rd, Mt Gravatt East QLD 4122', 'group' => 'contact'],
            ['key' => 'address_education_label', 'value' => '교육관', 'group' => 'contact'],
            ['key' => 'address_education', 'value' => '147 Kameruka St, Calamvale QLD 4116', 'group' => 'contact'],
            ['key' => 'contact_email', 'value' => 'juneunchurch@gmail.com', 'group' => 'contact'],
            ['key' => 'contact_phone', 'value' => '0415 346 455 (담임목사)', 'group' => 'contact'],

            ['key' => 'sunday_first_service_name', 'value' => '주일 1부 예배 (사역자 예배)', 'group' => 'service_times'],
            ['key' => 'sunday_first_service_time', 'value' => '주일 오전 10:30', 'group' => 'service_times'],
            ['key' => 'sunday_first_service_venue', 'value' => '교육관', 'group' => 'service_times'],
            ['key' => 'sunday_service_name', 'value' => '주일 2부 예배 (청장년부)', 'group' => 'service_times'],
            ['key' => 'sunday_service_time', 'value' => '주일 오후 1:30', 'group' => 'service_times'],
            ['key' => 'sunday_service_venue', 'value' => '본당 · Worship Centre', 'group' => 'service_times'],
            ['key' => 'wednesday_service_name', 'value' => '수요기도회', 'group' => 'service_times'],
            ['key' => 'wednesday_service_time', 'value' => '수요일 오후 7:30', 'group' => 'service_times'],
            ['key' => 'wednesday_service_venue', 'value' => '교육관', 'group' => 'service_times'],
            ['key' => 'kids_service_name', 'value' => '유초등부', 'group' => 'service_times'],
            ['key' => 'kids_service_time', 'value' => '주일 오후 1:30', 'group' => 'service_times'],
            ['key' => 'kids_service_venue', 'value' => '본당 · Hall', 'group' => 'service_times'],
            ['key' => 'youth_service_name', 'value' => '청소년부', 'group' => 'service_times'],
            ['key' => 'youth_service_time', 'value' => '주일 오후 1:30', 'group' => 'service_times'],
            ['key' => 'youth_service_venue', 'value' => '본당 · Chapel', 'group' => 'service_times'],

            ['key' => 'instagram_url', 'value' => 'https://www.instagram.com/juneun.church_brisbane/', 'group' => 'social'],
            ['key' => 'youtube_url', 'value' => 'https://www.youtube.com/@juneun_church', 'group' => 'social'],

            ['key' => 'home_hero_photo', 'value' => '', 'group' => 'home'],
            ['key' => 'highlight_photo', 'value' => '', 'group' => 'home'],
            ['key' => 'highlight_title', 'value' => '', 'group' => 'home'],
            ['key' => 'highlight_body', 'value' => '', 'group' => 'home'],
            ['key' => 'highlight_link_album', 'value' => '', 'group' => 'home'],
            ['key' => 'highlight_stat1_value', 'value' => '', 'group' => 'home'],
            ['key' => 'highlight_stat1_label', 'value' => '', 'group' => 'home'],
            ['key' => 'highlight_stat2_value', 'value' => '', 'group' => 'home'],
            ['key' => 'highlight_stat2_label', 'value' => '', 'group' => 'home'],

            ['key' => 'giving_bank', 'value' => 'Westpac', 'group' => 'giving'],
            ['key' => 'giving_account_name', 'value' => 'JU-NEUN PRESBYTERIAN CHURCH OF BRISBANE INC.', 'group' => 'giving'],
            ['key' => 'giving_bsb', 'value' => '034069', 'group' => 'giving'],
            ['key' => 'giving_account_number', 'value' => '615113', 'group' => 'giving'],
            ['key' => 'giving_kr_bank', 'value' => '카카오뱅크', 'group' => 'giving'],
            ['key' => 'giving_kr_account_name', 'value' => '', 'group' => 'giving'],
            ['key' => 'giving_kr_account_number', 'value' => '3333-31-2167745', 'group' => 'giving'],
        ];

        foreach ($settings as $setting) {
            SiteSetting::query()->firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
