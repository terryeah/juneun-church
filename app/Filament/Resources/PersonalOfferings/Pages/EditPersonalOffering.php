<?php

namespace App\Filament\Resources\PersonalOfferings\Pages;

use App\Filament\Resources\PersonalOfferings\PersonalOfferingResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPersonalOffering extends EditRecord
{
    protected static string $resource = PersonalOfferingResource::class;

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
