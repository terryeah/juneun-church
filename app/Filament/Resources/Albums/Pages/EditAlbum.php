<?php

namespace App\Filament\Resources\Albums\Pages;

use App\Filament\Resources\Albums\AlbumResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAlbum extends EditRecord
{
    protected static string $resource = AlbumResource::class;

    /**
     * A replaced cover invalidates its stored thumbnail.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['cover_photo_path'] ?? null) !== $this->record->cover_photo_path) {
            $data['cover_thumbnail_path'] = null;
        }

        return $data;
    }

    /**
     * Rebuild the cover thumbnail after changes.
     */
    protected function afterSave(): void
    {
        $this->record->refreshCoverThumbnail();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
