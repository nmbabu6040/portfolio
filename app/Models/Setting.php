<?php

namespace App\Models;

use App\Support\Activity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'name';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];
    private static ?array $memo = null;
    // all settings, read once per request and kept in the cache until a setting changes
    public static function map(): array
    {
        if (self::$memo !== null) return self::$memo;
        try {
            return self::$memo = Cache::rememberForever('settings.map', fn() => static::query()->pluck('value', 'name')->filter()->all());
        } catch (\Throwable $e) {
            return [];
        }
    }
    public static function forget(): void
    {
        self::$memo = null;
        try {
            Cache::forget('settings.map');
        } catch (\Throwable $e) {
        }
    }
    protected static function booted(): void
    {
        static::created(fn($m) => self::log($m, 'created'));
        static::updated(fn($m) => self::log($m, 'updated'));
        static::saved(fn() => self::forget());
        static::deleted(fn() => self::forget());
    }
    private static function log($m, string $event): void
    {
        if (!auth()->check() || $m->name === 'last_backup') return;
        if ($event === 'created' && ($m->value === null || $m->value === '')) return;
        Activity::note('updated', 'Changed setting: ' . $m->name, 'Settings');
    }
}
