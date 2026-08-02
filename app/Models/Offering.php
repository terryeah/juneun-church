<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A Sunday's offering record (헌금 내역): the same list the bulletin
 * prints - category, giver and amount - published on the giving page.
 */
#[Fillable(['sunday_date', 'items', 'note', 'created_by'])]
class Offering extends Model
{
    use HasFactory, LogsModelActivity;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sunday_date' => 'date',
            'items' => 'array',
        ];
    }

    /**
     * Offering categories offered in the admin form.
     *
     * @var array<string, string>
     */
    public const CATEGORIES = [
        '십일조' => '십일조',
        '감사헌금' => '감사헌금',
        '주일헌금' => '주일헌금',
        '선교헌금' => '선교헌금',
        '목적헌금' => '목적헌금',
        '절기헌금' => '절기헌금',
        '기타' => '기타',
    ];

    /**
     * Items grouped by category, preserving the form order.
     *
     * @return array<string, array<int, array{category: string, name: ?string, amount: ?string}>>
     */
    public function groupedItems(): array
    {
        return collect($this->items ?? [])->groupBy('category')->toArray();
    }

    /**
     * The week's total across all items with an amount.
     */
    public function total(): float
    {
        return collect($this->items ?? [])->sum(fn (array $item): float => (float) ($item['amount'] ?? 0));
    }
}
