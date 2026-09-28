<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'label'];

    /**
     * Cache TTL in seconds (10 minutes).
     */
    private static int $cacheTtl = 600;

    /**
     * Get a setting value by key, with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::allCached();
        return $all[$key] ?? $default;
    }

    /**
     * Persist a setting value and bust the cache.
     */
    public static function set(string $key, mixed $value, string $group = 'general', string $label = ''): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'label' => $label]
        );
        Cache::forget('app_settings_all');
    }

    /**
     * Get all settings for a specific group as key→value array.
     */
    public static function group(string $group): array
    {
        $all = static::allCached();
        $rows = static::where('group', $group)->get();
        $result = [];
        foreach ($rows as $row) {
            $result[$row->key] = $all[$row->key] ?? null;
        }
        return $result;
    }

    /**
     * Load all settings from cache or database.
     */
    public static function allCached(): array
    {
        return Cache::remember('app_settings_all', static::$cacheTtl, function () {
            return static::all()->pluck('value', 'key')->toArray();
        });
    }

    /**
     * Invalidate the settings cache (called automatically on set()).
     */
    public static function clearCache(): void
    {
        Cache::forget('app_settings_all');
    }
}
