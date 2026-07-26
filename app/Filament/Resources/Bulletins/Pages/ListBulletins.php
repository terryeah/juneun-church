<?php

namespace App\Filament\Resources\Bulletins\Pages;

use App\Filament\Resources\Bulletins\BulletinResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBulletins extends ListRecords
{
    protected static string $resource = BulletinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
