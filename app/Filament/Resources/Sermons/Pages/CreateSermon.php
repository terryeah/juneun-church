<?php

namespace App\Filament\Resources\Sermons\Pages;

use App\Filament\Resources\Sermons\SermonResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Create page that records the authoring user.
 */
class CreateSermon extends CreateRecord
{
    protected static string $resource = SermonResource::class;

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
