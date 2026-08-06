<?php

namespace App\Models\Concerns;

use App\Services\CloudflareCachePurger;
use Illuminate\Database\Eloquent\Model;

/**
 * Takes a record's media off the CDN when the record is deleted or its
 * file is swapped for another.
 *
 * Deleting removes the object from R2; replacing writes the new upload
 * under a fresh UUID and leaves the old key behind. Either way the URL
 * that was already handed out stays cached at the edge for a year
 * unless it is purged, so both events are covered.
 */
trait PurgesCdnCache
{
    /**
     * Register the delete and replace hooks.
     */
    protected static function bootPurgesCdnCache(): void
    {
        static::deleted(function (Model $model): void {
            CloudflareCachePurger::forget(array_map(
                fn (string $column): ?string => $model->getAttribute($column),
                $model->cdnMediaColumns(),
            ));
        });

        static::updated(function (Model $model): void {
            CloudflareCachePurger::forget(array_map(
                fn (string $column): ?string => $model->wasChanged($column)
                    ? $model->getOriginal($column)
                    : null,
                $model->cdnMediaColumns(),
            ));
        });
    }

    /**
     * The columns holding the paths of objects served from the CDN.
     *
     * @return list<string>
     */
    abstract public function cdnMediaColumns(): array;
}
