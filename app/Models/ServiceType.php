<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A worship service type (주일예배, 수요예배, 금요기도회, 특별예배).
 */
#[Fillable(['name', 'sort_order'])]
class ServiceType extends Model
{
    use HasFactory, LogsModelActivity;

    /**
     * The sermons recorded under this service type.
     */
    public function sermons(): HasMany
    {
        return $this->hasMany(Sermon::class);
    }
}
