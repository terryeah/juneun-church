<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A ministry or department (부서 / 사역) such as 찬양팀 or 안내팀.
 *
 * Staff members reference these by name through their department field.
 */
#[Fillable(['name', 'description', 'sort_order'])]
class Ministry extends Model
{
    use HasFactory, LogsModelActivity;

    /**
     * Keep the string references on staff and members in step when a
     * ministry is renamed or removed.
     */
    protected static function booted(): void
    {
        static::updated(function (Ministry $ministry): void {
            if ($ministry->wasChanged('name')) {
                $original = $ministry->getOriginal('name');
                Member::query()->where('department', $original)->update(['department' => $ministry->name]);
            }
        });

        static::deleted(function (Ministry $ministry): void {
            Member::query()->where('department', $ministry->name)->update(['department' => null]);
        });
    }
}
