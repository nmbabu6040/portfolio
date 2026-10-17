<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\{DB, Mail, Schema};

class Backup
{
    public static function dir(): string
    {
        $d = storage_path('app/backups');
        if (!is_dir($d)) mkdir($d, 0755, true);
        return $d;
    }
    public static function tables(): array
    {
        return array_merge(['users', 'settings', 'messages', 'subscribers', 'comments', 'post_likes'], array_keys(config('admin')));
    }
    public static function sql(array $skip = []): string
    {
        $pdo = DB::connection()->getPdo();
        $sql = '-- Portfolio backup ' . now()->toDateTimeString() . "\nSET FOREIGN_KEY_CHECKS=0;\n";
        foreach (self::tables() as $t) {
            if (in_array($t, $skip) || !Schema::hasTable($t)) continue;
            foreach (DB::table($t)->cursor() as $row) {
                $row = (array) $row;
                $cols = implode(',', array_map(fn($c) => "`$c`", array_keys($row)));
                $vals = implode(',', array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), $row));
                $sql .= "INSERT INTO `$t` ($cols) VALUES ($vals);\n";
            }
        }
        return $sql . "SET FOREIGN_KEY_CHECKS=1;\n";
    }
    public static function zip(string $path, bool $files = true): void
    {
        $zip = new \ZipArchive;
        if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) throw new \RuntimeException('Could not create the zip file.');
        $zip->addFromString('database.sql', self::sql());
        $dir = storage_path('app/public');
        if ($files && is_dir($dir)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $f) {
                if ($f->isFile()) $zip->addFile($f->getPathname(), 'uploads/' . ltrim(str_replace('\\', '/', substr($f->getPathname(), strlen($dir))), '/'));
            }
        }
        $zip->close();
    }
    public static function human(int $b): string
    {
        return $b >= 1048576 ? number_format($b / 1048576, 1) . ' MB' : number_format(max($b, 1) / 1024, 0) . ' KB';
    }
    public static function list(): array
    {
        $out = [];
        foreach (glob(self::dir() . '/portfolio-*.zip') ?: [] as $f) $out[] = ['name' => basename($f), 'size' => filesize($f), 'time' => filemtime($f)];
        usort($out, fn($a, $b) => $b['time'] <=> $a['time']);
        return $out;
    }
    public static function prune(int $keep = 4): void
    {
        foreach (array_slice(self::list(), $keep) as $f) @unlink(self::dir() . '/' . $f['name']);
    }
    public static function run(bool $email = true, string $by = 'Automatic backup'): array
    {
        @set_time_limit(300);
        $save = fn(string $v) => DB::table('settings')->updateOrInsert(['name' => 'last_auto_backup'], ['value' => now()->format('d M Y, H:i') . ' | ' . $v]);
        try {
            $name = 'portfolio-' . now()->format('Y-m-d-His') . '.zip';
            self::zip(self::dir() . '/' . $name, true);
            self::prune(4);
            $note = "Backup saved on the server ($name, " . self::human(filesize(self::dir() . '/' . $name)) . ').';
            if ($email) {
                $m = self::mail();
                $note .= $m === true ? ' Database copy emailed.' : " Email not sent: $m";
            }
            $save($note);
            Activity::note('backup', "$by: $note", 'Backup', null, (object) ['id' => null, 'name' => $by]);
            return ['ok' => true, 'message' => $note];
        } catch (\Throwable $e) {
            report($e);
            $note = 'Backup failed: ' . $e->getMessage();
            $save($note);
            Activity::note('backup', $note, 'Backup', null, (object) ['id' => null, 'name' => $by]);
            return ['ok' => false, 'message' => $note];
        }
    }
    private static function mail(): bool|string
    {
        $s = Setting::map();
        if (empty($s['mail_host']) || empty($s['email'])) return 'the SMTP settings or the contact email are not set.';
        $tmp = tempnam(sys_get_temp_dir(), 'bkm');
        try {
            $z = new \ZipArchive;
            $z->open($tmp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            $z->addFromString('database.sql', self::sql(['users']));
            $z->close();
            $size = filesize($tmp);
            if ($size > 15 * 1024 * 1024) return 'the database copy is too large to email (' . self::human($size) . ').';
            MailConfig::apply($s);
            config(['mail.mailers.smtp.timeout' => 25]);
            $site = $s['name'] ?? config('app.name');
            Mail::raw("Here is the weekly database copy of $site.\n\nIt contains your content, settings, messages, comments and subscribers. For safety it does not include user accounts. The full backup (with images and user accounts) is on the server in storage/app/backups and in Admin > Backup.\n\nKeep this email private.", fn($m) => $m->to($s['email'])->subject("Weekly backup of $site")->attach($tmp, ['as' => 'database-' . now()->format('Y-m-d') . '.zip', 'mime' => 'application/zip']));
            return true;
        } catch (\Throwable $e) {
            report($e);
            return $e->getMessage();
        } finally {
            @unlink($tmp);
        }
    }
}
