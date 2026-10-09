<?php

namespace App\Support;

class Html
{
    const TAGS = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'h1', 'h2', 'h3', 'h4', 'ul', 'ol', 'li', 'blockquote', 'a', 'img', 'pre', 'code', 'span'];
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
        return strip_tags($b) === $b ? nl2br(e($b)) : self::clean($b);
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
                $c->setAttribute('rel', 'noopener noreferrer');
                $c->setAttribute('target', '_blank');
            }
            self::walk($c);
        }
    }
}
