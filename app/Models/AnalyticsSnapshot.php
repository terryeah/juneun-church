<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * A single day of Cloudflare zone analytics, snapshotted locally.
 */
#[Fillable([
    'snapshot_date',
    'requests',
    'page_views',
    'unique_visitors',
    'bytes',
    'cached_requests',
    'threats',
])]
class AnalyticsSnapshot extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'snapshot_date' => 'date',
        ];
    }
}
