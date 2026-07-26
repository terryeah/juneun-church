<?php

namespace Database\Seeders;

use App\Models\Album;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\Photo;
use App\Models\Position;
use App\Models\Sermon;
use App\Models\ServiceType;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Seeds demonstration content for local development and design review.
 *
 * Everything created here is plausible placeholder data so the public
 * pages render with realistic density. It never runs in production.
 */
class DemoContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::query()->first();

        $this->seedAnnouncements($admin);
        $this->seedEvents($admin);
        $this->seedSermons($admin);
        $this->seedStaff();
        $this->seedAlbums($admin);
    }

    /**
     * Seed a handful of announcements, the first of them pinned.
     */
    private function seedAnnouncements(?User $admin): void
    {
        if (Announcement::query()->exists()) {
            return;
        }

        $items = [
            ['title' => '2026년 하반기 교회 소그룹 안내', 'pinned' => true, 'days' => 2],
            ['title' => '반찬나눔 봉사자 모집', 'pinned' => false, 'days' => 6],
            ['title' => '주일학교 여름 성경학교 등록 안내', 'pinned' => false, 'days' => 12],
            ['title' => '교회 창립 2주년 감사예배 안내', 'pinned' => false, 'days' => 20],
        ];

        foreach ($items as $item) {
            Announcement::query()->create([
                'title' => $item['title'],
                'content' => '<p>'.$item['title'].'에 대한 자세한 내용입니다. 성도 여러분의 많은 관심과 참여 부탁드립니다.</p><p>문의는 교회 사무실로 연락해 주세요.</p>',
                'is_published' => true,
                'is_pinned' => $item['pinned'],
                'published_at' => now()->subDays($item['days']),
                'created_by' => $admin?->id,
            ]);
        }
    }

    /**
     * Seed upcoming and recent events across two months.
     */
    private function seedEvents(?User $admin): void
    {
        if (Event::query()->exists()) {
            return;
        }

        $items = [
            ['title' => '전교인 야외예배', 'date' => now()->addDays(9), 'time' => '11:30', 'location' => 'Rocks Riverside Park'],
            ['title' => '청년부 수련회', 'date' => now()->addDays(16), 'time' => '18:00', 'location' => '교육관'],
            ['title' => '반찬나눔', 'date' => now()->addDays(23), 'time' => '10:00', 'location' => '본당 주방'],
            ['title' => '창립 2주년 감사예배', 'date' => now()->addMonth()->addDays(5), 'time' => '11:30', 'location' => '본당'],
            ['title' => '주일학교 여름 성경학교', 'date' => now()->addMonth()->addDays(12), 'time' => '09:30', 'location' => '교육관'],
            ['title' => '온가족 찬양의 밤', 'date' => now()->addMonth()->addDays(19), 'time' => '19:00', 'location' => '본당'],
        ];

        foreach ($items as $item) {
            Event::query()->create([
                'title' => $item['title'],
                'event_date' => $item['date'],
                'event_time' => $item['time'],
                'location' => $item['location'],
                'is_published' => true,
                'created_by' => $admin?->id,
            ]);
        }
    }

    /**
     * Seed recent sermon recordings.
     */
    private function seedSermons(?User $admin): void
    {
        if (Sermon::query()->exists()) {
            return;
        }

        $sunday = ServiceType::query()->where('name', '주일예배')->first();
        $wednesday = ServiceType::query()->where('name', '수요예배')->first();

        $items = [
            ['title' => '받은 은혜를 흘려보내는 삶', 'scripture' => '고린도후서 9:6-15', 'weeks' => 0, 'type' => $sunday],
            ['title' => '서로 사랑하라', 'scripture' => '요한복음 13:34-35', 'weeks' => 1, 'type' => $sunday],
            ['title' => '광야에서 만나는 하나님', 'scripture' => '출애굽기 16:1-12', 'weeks' => 1, 'type' => $wednesday],
            ['title' => '믿음의 반석 위에', 'scripture' => '마태복음 7:24-27', 'weeks' => 2, 'type' => $sunday],
        ];

        foreach ($items as $item) {
            Sermon::query()->create([
                'title' => $item['title'],
                'youtube_video_id' => 'jfKfPfyJRdk',
                'preacher' => '엄현준 담임목사',
                'sermon_date' => now()->startOfWeek()->subWeeks($item['weeks'])->subDay(),
                'service_type_id' => $item['type']?->id ?? ServiceType::query()->value('id'),
                'scripture_reference' => $item['scripture'],
                'is_published' => true,
                'created_by' => $admin?->id,
            ]);
        }
    }

    /**
     * Seed the serving-members page.
     */
    private function seedStaff(): void
    {
        if (StaffMember::query()->exists()) {
            return;
        }

        $byName = Position::query()->pluck('id', 'name');

        $items = [
            ['name' => '엄현준', 'position' => '담임목사', 'department' => null],
            ['name' => '김성실', 'position' => '전도사', 'department' => '주일학교'],
            ['name' => '박믿음', 'position' => '장로', 'department' => null],
            ['name' => '이소망', 'position' => '권사', 'department' => '반찬나눔'],
            ['name' => '최사랑', 'position' => '집사', 'department' => '찬양팀'],
            ['name' => '정온유', 'position' => '집사', 'department' => '미디어팀'],
        ];

        foreach ($items as $index => $item) {
            StaffMember::query()->create([
                'name' => $item['name'],
                'position_id' => $byName[$item['position']],
                'department' => $item['department'],
                'sort_order' => $index * 10,
                'is_published' => true,
            ]);
        }
    }

    /**
     * Seed albums with generated placeholder photographs.
     */
    private function seedAlbums(?User $admin): void
    {
        if (Album::query()->exists() || ! extension_loaded('gd')) {
            return;
        }

        $albums = [
            ['title' => '2026 부활절 연합예배', 'days' => 100],
            ['title' => '청년부 바베큐 교제', 'days' => 45],
            ['title' => '반찬나눔 현장', 'days' => 14],
        ];

        foreach ($albums as $albumIndex => $data) {
            $album = Album::query()->create([
                'title' => $data['title'],
                'description' => $data['title'].' 사진 모음입니다.',
                'event_date' => now()->subDays($data['days']),
                'is_published' => true,
                'created_by' => $admin?->id,
            ]);

            foreach (range(1, 6) as $photoIndex) {
                $path = $this->createPlaceholderImage($albumIndex, $photoIndex);

                Photo::query()->create([
                    'album_id' => $album->id,
                    'filename' => basename($path),
                    'original_filename' => 'demo-'.$photoIndex.'.jpg',
                    'path' => $path,
                    'width' => 1200,
                    'height' => 800,
                    'file_size' => Storage::disk(config('filesystems.media'))->size($path),
                    'sort_order' => $photoIndex * 10,
                    'uploaded_by' => $admin?->id,
                ]);
            }

            $album->update(['cover_photo_path' => $album->photos()->first()?->path]);
        }
    }

    /**
     * Generate a muted grayscale placeholder JPEG on the media disk.
     */
    private function createPlaceholderImage(int $albumIndex, int $photoIndex): string
    {
        $image = imagecreatetruecolor(1200, 800);
        $shade = 150 + (($albumIndex * 17 + $photoIndex * 23) % 70);
        $base = imagecolorallocate($image, $shade, $shade, $shade);
        imagefill($image, 0, 0, $base);

        $dark = imagecolorallocate($image, $shade - 60, $shade - 60, $shade - 60);
        imagefilledellipse($image, 300 + $photoIndex * 90, 400, 500, 500, $dark);

        ob_start();
        imagejpeg($image, null, 70);
        $contents = ob_get_clean();
        imagedestroy($image);

        $path = 'gallery/demo/'.Str::uuid().'.jpg';
        Storage::disk(config('filesystems.media'))->put($path, $contents);

        return $path;
    }
}
