<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key, $default = null)
    {
        return Cache::remember("system_setting_{$key}", 60, function () use ($key, $default) {
            $row = static::query()->where('key', $key)->first();

            return $row ? $row->value : $default;
        });
    }

    public static function setValue(string $key, $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value]
        );

        Cache::forget("system_setting_{$key}");
    }

    public static function isLoginPhotoCaptureEnabled(): bool
    {
        return (string) static::getValue('login_photo_capture_enabled', '0') === '1';
    }
}
