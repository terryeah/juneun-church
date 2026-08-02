<?php

namespace App\Filament\Resources\Cells\Pages;

use App\Filament\Resources\Cells\CellResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCells extends ListRecords
{
    protected static string $resource = CellResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
