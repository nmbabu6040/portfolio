<?php

namespace App\Models;

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
}
