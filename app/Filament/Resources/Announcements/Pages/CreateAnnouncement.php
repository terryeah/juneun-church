<?php

namespace App\Filament\Resources\Announcements\Pages;

use App\Filament\Resources\Announcements\AnnouncementResource;
use App\Filament\Resources\Announcements\Pages\Concerns\ConfirmsHighlightTakeover;
use Filament\Resources\Pages\CreateRecord;

/**
 * Create page that records the authoring user.
 */
class CreateAnnouncement extends CreateRecord
{
    use ConfirmsHighlightTakeover;

    protected static bool $canCreateAnother = false;

    protected static string $resource = AnnouncementResource::class;

    /**
     * Page heading shown above the form.
     */
    public function getTitle(): string
    {
        return '새로운 소식';
    }

    /**
     * Breadcrumb label for this page.
     */
    protected static ?string $breadcrumb = '새로운 소식';

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
