<?php

namespace App\Filament\Resources\Photos\Pages;

use App\Filament\Resources\Photos\PhotoResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Create page that fills photo metadata from the uploaded file.
 */
class CreatePhoto extends CreateRecord
{
    protected static string $resource = PhotoResource::class;

    /**
     * Derive filename metadata and stamp the uploading user.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['filename'] = basename((string) $data['path']);
        $data['original_filename'] = $data['original_filename'] ?? $data['filename'];
        $data['uploaded_by'] = auth()->id();

        return $data;
    }
}
