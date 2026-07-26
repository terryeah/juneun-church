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
            ['key' => 'denomination', 'value' => '대한예수교 장로회', 'group' => 'contact'],
            ['key' => 'address_main_label', 'value' => '본관', 'group' => 'contact'],
            ['key' => 'address_main', 'value' => '71 Newnham Rd, Mt Gravatt East QLD 4122', 'group' => 'contact'],
            ['key' => 'address_education_label', 'value' => '교육관', 'group' => 'contact'],
            ['key' => 'address_education', 'value' => '147 Kameruka St, Calamvale QLD 4116', 'group' => 'contact'],
            ['key' => 'contact_email', 'value' => 'hello@juneunchurch.org.au', 'group' => 'contact'],
            ['key' => 'contact_phone', 'value' => '', 'group' => 'contact'],

            ['key' => 'sunday_service_name', 'value' => '주일예배', 'group' => 'service_times'],
            ['key' => 'sunday_service_time', 'value' => '주일 오후 1:30', 'group' => 'service_times'],
            ['key' => 'sunday_service_venue', 'value' => '본관', 'group' => 'service_times'],
            ['key' => 'wednesday_service_name', 'value' => '수요기도회', 'group' => 'service_times'],
            ['key' => 'wednesday_service_time', 'value' => '수요일 오후 7:30', 'group' => 'service_times'],
            ['key' => 'wednesday_service_venue', 'value' => '교육관', 'group' => 'service_times'],
            ['key' => 'kids_service_name', 'value' => '주일학교', 'group' => 'service_times'],
            ['key' => 'kids_service_time', 'value' => '주일 오후 1:30', 'group' => 'service_times'],
            ['key' => 'kids_service_venue', 'value' => '본관', 'group' => 'service_times'],

            ['key' => 'instagram_url', 'value' => 'https://www.instagram.com/juneun.church_brisbane/', 'group' => 'social'],
            ['key' => 'youtube_url', 'value' => 'https://www.youtube.com/@juneun_church', 'group' => 'social'],

            ['key' => 'giving_bank', 'value' => 'Westpac', 'group' => 'giving'],
            ['key' => 'giving_account_name', 'value' => 'Brisbane Juneun Church', 'group' => 'giving'],
            ['key' => 'giving_bsb', 'value' => '034069', 'group' => 'giving'],
            ['key' => 'giving_account_number', 'value' => '615113', 'group' => 'giving'],
        ];

        foreach ($settings as $setting) {
            SiteSetting::query()->firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
