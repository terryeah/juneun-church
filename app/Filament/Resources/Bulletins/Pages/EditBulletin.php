<?php

namespace App\Filament\Resources\Bulletins\Pages;

use App\Filament\Resources\Bulletins\BulletinResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBulletin extends EditRecord
{
    protected static string $resource = BulletinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
