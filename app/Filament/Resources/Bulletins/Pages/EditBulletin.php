<?php

namespace App\Filament\Resources\Bulletins\Pages;

use App\Filament\Resources\Bulletins\BulletinResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBulletin extends EditRecord
{
    protected static string $resource = BulletinResource::class;

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
