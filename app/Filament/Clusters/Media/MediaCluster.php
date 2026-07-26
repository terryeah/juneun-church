<?php

namespace App\Filament\Clusters\Media;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

/**
 * Groups the gallery resources (albums and photos) under a single
 * "Media" entry in the panel navigation.
 */
class MediaCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Media';

    protected static ?string $clusterBreadcrumb = 'Media';
}
