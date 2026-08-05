<?php

namespace App\Filament\Resources\PersonalOfferings\Pages;

use App\Filament\Resources\PersonalOfferings\PersonalOfferingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPersonalOfferings extends ListRecords
{
    protected static string $resource = PersonalOfferingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
