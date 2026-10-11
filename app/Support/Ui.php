<?php

namespace App\Support;

use App\Models\Entry;

class Ui
{
    const DEV = ['HTML5' => 'html5', 'CSS3' => 'css3', 'JavaScript' => 'javascript', 'AJAX' => 'javascript', 'Bootstrap' => 'bootstrap', 'jQuery' => 'jquery', 'PHP' => 'php', 'Laravel' => 'laravel', 'MySQL' => 'mysql', 'Git' => 'git', 'GitHub' => 'github', 'VS Code' => 'vscode', 'Composer' => 'composer', 'npm' => 'npm', 'Apache' => 'apache', 'Adobe Photoshop' => 'photoshop', 'Adobe Illustrator' => 'illustrator', 'Canva' => 'canva', 'Google Analytics' => 'googleanalytics'];
    public static function list($v, string $sep = ','): array
    {
        return array_values(array_filter(array_map('trim', explode($sep, (string) $v))));
    }
    public static function logo(string $n): ?string
    {
        $k = self::DEV[$n] ?? null;
        return $k ? "https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/$k/$k-original.svg" : null;
    }
    public static function initials(string $n): string
    {
        return collect(explode(' ', $n))->map(fn($w) => mb_substr($w, 0, 1))->take(2)->join('');
    }
    public static function catTitle(?string $slug): ?string
    {
        static $m = null;
        if (!$slug) return null;
        if ($m === null) {
            try {
                $m = Entry::of('categories')->newQuery()->pluck('title', 'slug')->all();
            } catch (\Throwable $e) {
                $m = [];
            }
        }
        return $m[$slug] ?? null;
    }
    public static function icon(?string $c, string $t): string
    {
        if ($c && preg_match('/^bi-[a-z0-9-]+$/', $c)) return $c;
        $t = strtolower($t);
        return match (true) {
            str_contains($t, 'web') => 'bi-code-slash',
            str_contains($t, 'seo') => 'bi-search',
            str_contains($t, 'social') => 'bi-share',
            str_contains($t, 'marketing') => 'bi-megaphone',
            str_contains($t, 'graphic') => 'bi-palette',
            str_contains($t, 'data') => 'bi-database',
            default => 'bi-stars'
        };
    }
}
