<?php

namespace App\Models;

use App\Support\Activity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Entry extends Model
{
    protected $guarded = [];
    public static function of(string $table): static
    {
        return (new static)->setTable($table);
    }
    protected static function booted(): void
    {
        static::created(fn($m) => self::log($m, 'created'));
        static::updated(fn($m) => self::log($m, 'updated'));
        static::deleted(fn($m) => self::log($m, 'deleted'));
    }
    private static function log($m, string $event): void
    {
        if (!auth()->check()) return;
        $t = $m->getTable();
        if (in_array($t, ['page_views', 'activity_logs'])) return;
        $changed = array_values(array_diff(array_keys($m->getChanges()), ['updated_at']));
        if ($event === 'updated' && (!$changed || ($t === 'messages' && $changed === ['is_read']))) return;
        $label = config("admin.$t.label") ?? ucfirst($t);
        $title = $m->title ?? $m->name ?? $m->question ?? $m->email ?? $m->value ?? ('#' . $m->getKey());
        $verb = ['created' => 'Created', 'updated' => 'Updated', 'deleted' => 'Deleted'][$event];
        Activity::note($event, "$verb $label: " . Str::limit(strip_tags((string) $title), 80) . ($event === 'updated' ? ' (' . implode(', ', $changed) . ')' : ''), $label, $m->getKey());
    }
}
