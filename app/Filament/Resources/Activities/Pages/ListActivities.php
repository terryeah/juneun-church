<?php

namespace App\Filament\Resources\Activities\Pages;

use App\Filament\Resources\Activities\ActivityResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Read-only listing of the activity log.
 */
class ListActivities extends ListRecords
{
    protected static string $resource = ActivityResource::class;
}
