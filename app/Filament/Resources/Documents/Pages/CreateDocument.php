<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Filament\Resources\Documents\DocumentResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Create page that records the authoring user.
 */
class CreateDocument extends CreateRecord
{
    protected static bool $canCreateAnother = false;

    protected static string $resource = DocumentResource::class;

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
