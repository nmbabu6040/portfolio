<?php

namespace App\Support;

use App\Models\Setting;

class Html
{
    const TAGS = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'h1', 'h2', 'h3', 'h4', 'ul', 'ol', 'li', 'blockquote', 'a', 'img', 'pre', 'code', 'span'];
    private static ?array $dom = null;
    private static array $sp = [];
    public static function domains(): array
    {
        if (self::$dom === null) {
            $raw = Setting::map()['affiliate_domains'] ?? '';
            self::$dom = array_values(array_filter(array_map(fn($d) => strtolower(explode('/', preg_replace('#^(https?://)?(www\.)?#i', '', trim($d)))[0]), preg_split('/[\s,]+/', $raw))));
        }
        return self::$dom;
    }
    public static function clean(string $html): string
    {
        if (trim($html) === '') return '';
        $d = new \DOMDocument();
        libxml_use_internal_errors(true);
        $d->loadHTML('<?xml encoding="utf-8"?><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $root = $d->getElementsByTagName('div')->item(0);
        self::walk($root);
        $out = '';
        foreach ($root->childNodes as $c) $out .= $d->saveHTML($c);
        return $out;
    }
    public static function render(string $b): string
    {
        if (strip_tags($b) === $b) return nl2br(e($b));
        self::$sp = self::domains();
        $out = self::clean($b);
        self::$sp = [];
        return $out;
    }
    private static function walk(\DOMNode $n): void
    {
        foreach (iterator_to_array($n->childNodes) as $c) {
            if (!$c instanceof \DOMElement) continue;
            $t = strtolower($c->tagName);
            if (!in_array($t, self::TAGS, true)) {
                if (in_array($t, ['script', 'style', 'iframe', 'object', 'embed'], true)) {
                    $n->removeChild($c);
                    continue;
                }
                self::walk($c);
                while ($c->firstChild) $n->insertBefore($c->firstChild, $c);
                $n->removeChild($c);
                continue;
            }
            foreach (iterator_to_array($c->attributes) as $a) {
                $k = strtolower($a->name);
                $v = trim($a->value);
                $ok = ($t === 'a' && $k === 'href' && preg_match('#^(https?://|mailto:|/|\#)#i', $v))
                    || ($t === 'img' && $k === 'src' && preg_match('#^(https?://|/)#i', $v))
                    || ($t === 'img' && $k === 'alt');
                if (!$ok) $c->removeAttribute($a->name);
            }
            if ($t === 'a') {
                $host = strtolower((string) parse_url($c->getAttribute('href'), PHP_URL_HOST));
                $rel = 'noopener noreferrer';
                foreach (self::$sp as $d) {
                    if ($host !== '' && ($host === $d || str_ends_with($host, '.' . $d))) {
                        $rel = 'sponsored nofollow noopener noreferrer';
                        break;
                    }
                }
                $c->setAttribute('rel', $rel);
                $c->setAttribute('target', '_blank');
            }
            self::walk($c);
        }
    }
}
