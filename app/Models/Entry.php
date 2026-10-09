<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    protected $guarded = [];
    public static function of(string $table): static
    {
        return (new static)->setTable($table);
    }
}
