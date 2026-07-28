<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * A key-value site setting (contact details, service times, social links).
 *
 * Settings are cached as a single collection to avoid repeated queries in
 * the layout, and the cache is flushed whenever any setting is saved.
 */
#[Fillable(['key', 'value', 'group'])]
class SiteSetting extends Model
{
    use LogsModelActivity;

    /**
     * Flush the settings cache whenever a setting changes.
     */
    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('site_settings'));
        static::deleted(fn () => Cache::forget('site_settings'));
    }

    /**
     * Retrieve a setting value by key, with an optional default.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        /** @var array<string, string|null> $settings */
        $settings = Cache::rememberForever('site_settings', function (): array {
            return static::query()->pluck('value', 'key')->all();
        });

        return $settings[$key] ?? $default;
    }
}
