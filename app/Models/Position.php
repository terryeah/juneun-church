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
 * Positions are ordered with sort_order in Presbyterian rank order.
 */
#[Fillable(['name', 'sort_order'])]
class Position extends Model
{
    use HasFactory, LogsModelActivity;

    /**
     * Position names that make somebody a 목회자: the people a 기도제목
     * marked 목회자에게만 is for.
     *
     * @var list<string>
     */
    public const PASTORAL = ['담임목사', '부목사', '전도사'];

    /**
     * The staff members holding this position.
     */
    public function staffMembers(): HasMany
    {
        return $this->hasMany(Member::class)->orderBy('sort_order')->orderBy('name');
    }
}
