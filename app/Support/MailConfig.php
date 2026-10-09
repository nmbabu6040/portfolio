<?php

namespace App\Support;

use Illuminate\Support\Facades\Crypt;

class MailConfig
{
    public static function apply(array $s): void
    {
        if (empty($s['mail_host'])) return;
        try {
            $pw = !empty($s['mail_password']) ? Crypt::decryptString($s['mail_password']) : null;
        } catch (\Throwable $e) {
            $pw = null;
        }
        $enc = $s['mail_encryption'] ?? 'tls';
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $s['mail_host'],
            'mail.mailers.smtp.port' => (int) ($s['mail_port'] ?? 587),
            'mail.mailers.smtp.username' => $s['mail_username'] ?? null,
            'mail.mailers.smtp.password' => $pw,
            'mail.mailers.smtp.scheme' => $enc == 'ssl' ? 'smtps' : null,
            'mail.mailers.smtp.encryption' => $enc == 'none' ? null : $enc,
            'mail.from.address' => $s['mail_from'] ?? ($s['mail_username'] ?? config('mail.from.address')),
            'mail.from.name' => $s['name'] ?? config('mail.from.name'),
        ]);
    }
}
