<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Shared activity logging behaviour for content models.
 *
 * Records create, update and delete events with only the changed
 * fillable attributes, skipping empty logs and sensitive values.
 */
trait LogsModelActivity
{
    use LogsActivity;

    /**
     * Configure how model changes are written to the activity log.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->logExcept(['password', 'remember_token']);
    }
}
