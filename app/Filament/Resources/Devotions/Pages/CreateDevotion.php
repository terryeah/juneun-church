<?php

namespace App\Filament\Resources\Devotions\Pages;

use App\Filament\Resources\Devotions\DevotionResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Create page that records the authoring user.
 *
 * 저장 후 계속 만들기 stays on: a week of 큐티 is written in one sitting,
 * and the date field moves on to the next day each time.
 */
class CreateDevotion extends CreateRecord
{
    protected static string $resource = DevotionResource::class;

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
