<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type', 'description'];

    /**
     * Get a setting value by key, with caching.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = Cache::remember("setting.{$key}", 3600, function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value and clear cache.
     */
    public static function setValue(string $key, mixed $value): void
    {
        $setting = static::where('key', $key)->first();

        if ($setting) {
            $setting->update(['value' => is_array($value) ? json_encode($value) : (string) $value]);
        }

        Cache::forget("setting.{$key}");
        Cache::forget("settings.group.{$setting?->group}");
    }

    /**
     * Get all settings for a group.
     */
    public static function getGroup(string $group): array
    {
        $settings = Cache::remember("settings.group.{$group}", 3600, function () use ($group) {
            return static::where('group', $group)->get();
        });

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = static::castValue($setting->value, $setting->type);
        }

        return $result;
    }

    /**
     * Cast value to the correct PHP type.
     */
    protected static function castValue(?string $value, string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $value,
            'float'   => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($value, true),
            default   => $value,
        };
    }
}
