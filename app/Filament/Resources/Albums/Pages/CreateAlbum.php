<?php

namespace App\Filament\Resources\Albums\Pages;

use App\Filament\Resources\Albums\AlbumResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Create page that records the authoring user.
 */
class CreateAlbum extends CreateRecord
{
    protected static bool $canCreateAnother = false;

    protected static string $resource = AlbumResource::class;

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

    /**
     * Build the cover thumbnail once the album is stored.
     */
    protected function afterCreate(): void
    {
        $this->record->refreshCoverThumbnail();
    }
}
