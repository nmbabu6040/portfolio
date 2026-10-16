<?php

namespace App\Models;

use App\Support\Activity;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $primaryKey = 'name';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];
    public static function map(): array
    {
        try {
            return static::query()->pluck('value', 'name')->filter()->all();
        } catch (\Throwable $e) {
            return [];
        }
    }
    protected static function booted(): void
    {
        static::created(fn($m) => self::log($m, 'created'));
        static::updated(fn($m) => self::log($m, 'updated'));
    }
    private static function log($m, string $event): void
    {
        if (!auth()->check() || $m->name === 'last_backup') return;
        if ($event === 'created' && ($m->value === null || $m->value === '')) return;
        Activity::note('updated', 'Changed setting: ' . $m->name, 'Settings');
    }
}
