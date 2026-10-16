<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class Activity
{
    public static function note(string $action, string $summary, ?string $subject = null, $subjectId = null, $as = null): void
    {
        try {
            $u = $as ?? auth()->user();
            DB::table('activity_logs')->insert([
                'user_id' => $u->id ?? null,
                'user_name' => mb_substr($u->name ?? 'System', 0, 100),
                'action' => $action,
                'subject' => $subject ? mb_substr($subject, 0, 60) : null,
                'subject_id' => $subjectId,
                'summary' => mb_substr($summary, 0, 250),
                'ip' => request()->ip(),
                'created_at' => now(),
            ]);
            if (random_int(1, 100) === 1) DB::table('activity_logs')->where('created_at', '<', now()->subDays(180))->delete();
        } catch (\Throwable $e) {
            // logging must never break the site
        }
    }
}
