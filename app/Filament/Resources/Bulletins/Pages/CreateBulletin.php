<?php

namespace App\Filament\Resources\Bulletins\Pages;

use App\Filament\Resources\Bulletins\BulletinResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Create page that records the authoring user.
 */
class CreateBulletin extends CreateRecord
{
    protected static bool $canCreateAnother = false;

    protected static string $resource = BulletinResource::class;

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
