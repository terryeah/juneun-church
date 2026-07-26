<?php

namespace App\Filament\Resources\Announcements\Pages;

use App\Filament\Resources\Announcements\AnnouncementResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Create page that records the authoring user.
 */
class CreateAnnouncement extends CreateRecord
{
    protected static string $resource = AnnouncementResource::class;

    /**
     * Stamp the authenticated user as the record creator.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
