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
#[Fillable(['name', 'sort_order'])]
class Ministry extends Model
{
    use HasFactory, LogsModelActivity;
}
