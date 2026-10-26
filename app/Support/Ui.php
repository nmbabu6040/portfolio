<?php

namespace App\Support;

use App\Models\Entry;

class Ui
{
    const DEV = [
        'HTML5' => 'html5',
        'CSS3' => 'css3',
        'JavaScript' => 'javascript',
        'AJAX' => 'javascript',
        'Bootstrap' => 'bootstrap',
        'jQuery' => 'jquery',
        'PHP' => 'php',
        'Laravel' => 'laravel',
        'MySQL' => 'mysql',
        'Git' => 'git',
        'GitHub' => 'github',
        'VS Code' => 'vscode',
        'Composer' => 'composer',
        'npm' => 'npm',
        'Apache' => 'apache',
        'Adobe Photoshop' => 'photoshop',
        'Adobe Illustrator' => 'illustrator',
        'Canva' => 'canva',
        'Google Analytics' => 'googleanalytics'
    ];
    public static function list($v, string $sep = ','): array
    {
        return array_values(array_filter(array_map('trim', explode($sep, (string) $v))));
    }
    public static function logo(string $n): ?string
    {
        $k = self::DEV[$n] ?? null;
        if (!$k) return null;
        // return is_file(public_path("vendor/devicon/$k.svg")) ? asset("vendor/devicon/$k.svg") : "https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/$k/$k-original.svg";
        return is_file(public_path("vendor/devicon/$k.svg"))
            ? asset("vendor/devicon/$k.svg")
            : null;
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

    public static function v(string $rel): string
    {
        $p = public_path($rel);
        return asset($rel) . '?v=' . (is_file($p) ? filemtime($p) : '1');
    }
    public static function dims(string $rel): ?array
    {
        static $c = [];
        if (array_key_exists($rel, $c)) return $c[$rel];
        $p = public_path($rel);
        $i = is_file($p) ? @getimagesize($p) : false;
        return $c[$rel] = $i ? [$i[0], $i[1]] : null;
    }
    // responsive <picture>: $load = 'lazy' | 'eager' | 'high' (eager + fetchpriority high, for the main image of a page)
    public static function pic(?string $path, string $alt = '', string $sizes = '100vw', string $class = '', string $load = 'lazy'): string
    {
        if (!$path) return '';
        $d = self::dims($path);
        $attr = ' alt="' . e($alt) . '"' . ($class ? ' class="' . e($class) . '"' : '') . ($d ? ' width="' . $d[0] . '" height="' . $d[1] . '"' : '') . ' decoding="async"';

        // $attr .= $load === 'lazy' ? ' loading="lazy"' : ($load === 'high' ? ' fetchpriority="high"' : '');
        if ($load === 'lazy') {
            $attr .= ' loading="lazy"';
        } elseif ($load === 'high') {
            $attr .= ' loading="eager" fetchpriority="high"';
        } else {
            $attr .= ' loading="eager"';
        }

        $img = '<img src="' . e(asset($path)) . '"' . $attr . '>';
        if (!$d || !preg_match('/\.webp$/i', $path)) return $img;
        $base = substr($path, 0, -5);
        $set = function (string $ext) use ($base, $d) {
            $parts = [];
            foreach ([240, 480, 800, 1200] as $w) {
                if ($w < $d[0] && is_file(public_path("$base-$w.$ext"))) $parts[] = e(asset("$base-$w.$ext")) . " {$w}w";
            }
            if (is_file(public_path("$base.$ext"))) $parts[] = e(asset("$base.$ext")) . " {$d[0]}w";
            return (count($parts) > 1 || ($ext === 'avif' && $parts)) ? implode(', ', $parts) : null;
        };
        $out = '';
        foreach (['avif' => 'image/avif', 'webp' => 'image/webp'] as $ext => $type) {
            if ($s = $set($ext)) $out .= '<source type="' . $type . '" srcset="' . $s . '" sizes="' . e($sizes) . '">';
        }
        return $out === '' ? $img : '<picture>' . $out . $img . '</picture>';
    }

    // attributes for a "Book a call" link: opens /book if the calendar is embedded, otherwise the calendar in a new tab
    public static function book(array $s): string
    {
        $u = $s['booking_url'] ?? '';
        if ($u === '') return '';
        return !empty($s['booking_embed']) ? ' href="' . e(url('/book')) . '" data-cta="book"' : ' href="' . e($u) . '" target="_blank" rel="noopener noreferrer" data-cta="book"';
    }

    // HTML (from the editor) to clean one-line text, for meta tags, cards and sub-headings
    public static function plain($html, int $limit = 0): string
    {
        $t = preg_replace('/<\/(p|div|li|h[1-6]|blockquote|pre|ul|ol)>|<br\s*\/?>/i', ' ', (string) $html);
        $t = trim(preg_replace('/[\s\x{00A0}]+/u', ' ', html_entity_decode(strip_tags($t), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        return $limit ? \Illuminate\Support\Str::limit($t, $limit) : $t;
    }
    // editor HTML is shown as safe HTML, old plain text is split into paragraphs
    public static function rich($v): string
    {
        $v = (string) $v;
        if (trim($v) === '') return '';
        if (strip_tags($v) === $v) return collect(preg_split('/\R{2,}/', trim($v)))->map(fn($p) => '<p>' . nl2br(e(trim($p))) . '</p>')->implode('');
        return Html::render($v);
    }

    // local copy in public/vendor if it was downloaded (php artisan assets:vendor), otherwise the CDN address
    public static function lib(string $rel, string $cdn): string
    {
        return is_file(public_path("vendor/$rel")) ? self::v("vendor/$rel") : $cdn;
    }
    public static function selfFonts(): bool
    {
        return is_file(public_path('vendor/fonts.css')) && is_file(public_path('vendor/fonts/inter.woff2'));
    }

    // favicon tags: small PNG copies are made once from the uploaded image
    public static function favicon(array $s): array
    {
        $p = $s['favicon'] ?? null;
        if (!$p) return is_file(public_path('favicon.ico')) ? ['icon' => asset('favicon.ico'), 'type' => 'image/x-icon', 'apple' => null] : ['icon' => null, 'type' => null, 'apple' => null];
        $base = preg_replace('/\.[a-z0-9]+$/i', '', $p);
        $f48 = "$base-fav48.png";
        $f180 = "$base-fav180.png";
        if (!is_file(public_path($f48)) || !is_file(public_path($f180))) {
            try {
                Img::icons($p);
            } catch (\Throwable $e) {
            }
        }
        if (is_file(public_path($f48)) && is_file(public_path($f180))) return ['icon' => asset($f48), 'type' => 'image/png', 'apple' => asset($f180)];
        $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
        return ['icon' => asset($p), 'type' => ['png' => 'image/png', 'webp' => 'image/webp', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'ico' => 'image/x-icon'][$ext] ?? null, 'apple' => asset($p)];
    }
}
