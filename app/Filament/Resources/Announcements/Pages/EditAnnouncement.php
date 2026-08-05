<?php

namespace App\Filament\Resources\Announcements\Pages;

use App\Filament\Resources\Announcements\AnnouncementResource;
use App\Filament\Resources\Announcements\Pages\Concerns\ConfirmsHighlightTakeover;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAnnouncement extends EditRecord
{
    use ConfirmsHighlightTakeover;

    protected static string $resource = AnnouncementResource::class;

    /**
     * Keep the delete action at the bottom of the form, next to 취소,
     * instead of in the page header.
     *
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            ...parent::getFormActions(),
            DeleteAction::make(),
        ];
    }
}
