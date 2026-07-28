<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A church position (직분) such as 담임목사, 장로 or 집사.
 *
 * Positions are grouped by category (pastoral, elder, deacon, volunteer)
 * and ordered with sort_order to drive the hierarchical staff page.
 */
#[Fillable(['name', 'category', 'sort_order'])]
class Position extends Model
{
    use HasFactory, LogsModelActivity;

    /**
     * The staff members holding this position.
     */
    public function staffMembers(): HasMany
    {
        return $this->hasMany(StaffMember::class)->orderBy('sort_order');
    }
}
