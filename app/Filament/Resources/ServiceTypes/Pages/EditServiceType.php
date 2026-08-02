<?php

namespace App\Filament\Resources\ServiceTypes\Pages;

use App\Filament\Resources\ServiceTypes\ServiceTypeResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditServiceType extends EditRecord
{
    protected static string $resource = ServiceTypeResource::class;

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
