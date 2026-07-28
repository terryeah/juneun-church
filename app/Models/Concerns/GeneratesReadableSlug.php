<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Generates readable English slugs.
 *
 * When a title produces a usable Latin slug it is used directly;
 * otherwise (for example Korean-only titles, which slugify to nothing)
 * a meaningful dated fallback such as "news-20260728" is generated.
 * A numeric suffix guarantees uniqueness in both cases.
 */
trait GeneratesReadableSlug
{
    /**
     * Build a unique slug from a title with a dated English fallback.
     */
    protected static function readableSlug(string $title, string $prefix, string $date): string
    {
        $base = Str::slug($title);

        if (mb_strlen($base) < 4) {
            $base = $prefix.'-'.$date;
        }

        $slug = $base;
        $suffix = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
