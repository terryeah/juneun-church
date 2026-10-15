<?php

namespace App\Filament\Resources\Devotions\Pages;

use App\Filament\Resources\Devotions\DevotionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDevotions extends ListRecords
{
    protected static string $resource = DevotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
