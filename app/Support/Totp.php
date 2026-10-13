<?php

namespace App\Support;

class Totp
{
    const ALPHA = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    public static function secret(): string
    {
        return self::b32(random_bytes(20));
    }
    public static function b32(string $bin): string
    {
        $bits = '';
        foreach (str_split($bin) as $c) $bits .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT);
        $out = '';
        foreach (str_split($bits, 5) as $ch) $out .= self::ALPHA[bindec(str_pad($ch, 5, '0'))];
        return $out;
    }
    public static function unb32(string $s): string
    {
        $bits = '';
        foreach (str_split(strtoupper(preg_replace('/[^A-Z2-7]/i', '', $s))) as $c) $bits .= str_pad(decbin(strpos(self::ALPHA, $c)), 5, '0', STR_PAD_LEFT);
        $out = '';
        foreach (str_split($bits, 8) as $b) if (strlen($b) === 8) $out .= chr(bindec($b));
        return $out;
    }
    public static function code(string $secret, int $counter): string
    {
        $h = hash_hmac('sha1', pack('N*', 0, $counter), self::unb32($secret), true);
        $o = ord($h[19]) & 0xf;
        $n = ((ord($h[$o]) & 0x7f) << 24) | ((ord($h[$o + 1]) & 0xff) << 16) | ((ord($h[$o + 2]) & 0xff) << 8) | (ord($h[$o + 3]) & 0xff);
        return str_pad((string) ($n % 1000000), 6, '0', STR_PAD_LEFT);
    }
    // returns the matched time step, or null. A step can be used only once ($after = last used step)
    public static function verify(string $secret, string $code, int $after = 0): ?int
    {
        $code = preg_replace('/\D/', '', $code);
        if (strlen($code) !== 6) return null;
        $now = intdiv(time(), 30);
        for ($i = -1; $i <= 1; $i++) {
            $c = $now + $i;
            if ($c > $after && hash_equals(self::code($secret, $c), $code)) return $c;
        }
        return null;
    }
    public static function uri(string $secret, string $account, string $issuer): string
    {
        return 'otpauth://totp/' . rawurlencode($issuer . ':' . $account) . '?secret=' . $secret . '&issuer=' . rawurlencode($issuer) . '&algorithm=SHA1&digits=6&period=30';
    }
}
