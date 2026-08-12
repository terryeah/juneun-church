<?php

namespace App\Filament\Resources\Videos\Pages;

use App\Filament\Resources\Videos\VideoResource;
use App\Models\Video;
use Filament\Resources\Pages\CreateRecord;

class CreateVideo extends CreateRecord
{
    protected static string $resource = VideoResource::class;

    /**
     * Stamp the administrator, and put the video at the end.
     *
     * A default of 0 would put every new video in front of the ones
     * already there, so adding this year's 수련회 would push it above
     * the 2024 one. Newest last is what the office means by adding.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        if (blank($data['sort_order'] ?? null)) {
            $data['sort_order'] = 1 + (int) Video::query()
                ->where('album_id', $data['album_id'])
                ->max('sort_order');
        }

        return $data;
    }
}
