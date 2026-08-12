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
     * The settings for this request, once they have been fetched.
     *
     * rememberForever() saves the database query, not the round trip:
     * the cache store is itself a database table here, so every call
     * was a SELECT against `cache` plus an unserialize. The footer alone
     * asks sixteen times, and 오시는 길 asked fifty-two times in all -
     * for one row of settings that cannot change mid-request.
     *
     * @var array<string, string|null>|null
     */
    private static ?array $settings = null;

    /**
     * Flush the settings cache whenever a setting changes.
     */
    protected static function booted(): void
    {
        static::saved(fn () => static::flush());
        static::deleted(fn () => static::flush());
    }

    /**
     * Drop both the shared cache and this request's copy of it.
     */
    public static function flush(): void
    {
        Cache::forget('site_settings');
        static::$settings = null;
    }

    /**
     * Retrieve a setting value by key, with an optional default.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        static::$settings ??= Cache::rememberForever('site_settings', function (): array {
            return static::query()->pluck('value', 'key')->all();
        });

        return static::$settings[$key] ?? $default;
    }
}
