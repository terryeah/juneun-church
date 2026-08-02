<?php

namespace App\Filament\Resources\Cells\Pages;

use App\Filament\Resources\Cells\CellResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCell extends CreateRecord
{
    protected static bool $canCreateAnother = false;

    protected static string $resource = CellResource::class;
}
