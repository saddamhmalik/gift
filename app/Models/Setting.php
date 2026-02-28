<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type', 'label', 'description'];

    protected const CACHE_KEY = 'app_settings';
    protected const CACHE_TTL = 3600; // 1 hour

    /**
     * Get a setting value by key, cast to its stored type.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::allCached();
        if (! array_key_exists($key, $all)) {
            return $default;
        }
        return $all[$key];
    }

    /**
     * Set a setting value and bust the cache.
     */
    public static function set(string $key, mixed $value): void
    {
        static::where('key', $key)->update(['value' => $value]);
        Cache::forget(static::CACHE_KEY);
    }

    /**
     * All settings as key → cast-value array, cached.
     */
    public static function allCached(): array
    {
        return Cache::remember(static::CACHE_KEY, static::CACHE_TTL, function () {
            return static::all()->mapWithKeys(function ($s) {
                return [$s->key => static::cast($s->value, $s->type)];
            })->toArray();
        });
    }

    /**
     * Clear the settings cache (call after any update).
     */
    public static function clearCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }

    /**
     * Cast a raw string value to the correct PHP type.
     */
    protected static function cast(mixed $value, string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $value,
            'float'   => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            default   => $value,
        };
    }
}
