<?php

namespace App\Filament\Resources\PersonalOfferings\Pages;

use App\Filament\Resources\PersonalOfferings\PersonalOfferingResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePersonalOffering extends CreateRecord
{
    protected static string $resource = PersonalOfferingResource::class;

    protected static bool $canCreateAnother = false;
}
