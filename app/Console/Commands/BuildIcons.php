<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\{DB, Http};

class BuildIcons extends Command
{
    protected $signature = 'assets:icons';
    protected $description = 'Build a small icon stylesheet with only the Bootstrap icons the public site uses';
    // icons chosen in code with a variable, for example bi-{{ $k }}
    const EXTRA = ['linkedin', 'github', 'facebook', 'twitter-x', 'telegram', 'whatsapp', 'youtube', 'instagram', 'arrow-up'];
    public function handle(): int
    {
        $files = [app_path('Support/Ui.php'), public_path('js/site.js')];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views'), \FilesystemIterator::SKIP_DOTS)) as $f) {
            $p = str_replace('\\', '/', $f->getPathname());
            if ($f->isFile() && str_ends_with($p, '.blade.php') && !str_contains($p, '/views/admin/')) $files[] = $f->getPathname();
        }
        $names = collect(self::EXTRA);
        foreach ($files as $f) {
            if (!is_file($f)) continue;
            preg_match_all('/\bbi-([a-z0-9]+(?:-[a-z0-9]+)*)/', file_get_contents($f), $m);
            $names = $names->merge($m[1]);
        }
        try {
            foreach (DB::table('services')->pluck('icon') as $i) if ($i && preg_match('/^bi-([a-z0-9-]+)$/', $i, $m)) $names->push($m[1]);
        } catch (\Throwable $e) {
        }
        $names = $names->unique()->sort()->values();
        $css = ".bi{display:inline-block;line-height:1;vertical-align:-.125em}\n"
            . ".bi::before{content:\"\";display:block;width:1em;height:1em;background-color:currentColor;-webkit-mask:var(--bi) center/contain no-repeat;mask:var(--bi) center/contain no-repeat}\n";
        $ok = 0;
        $bad = [];
        foreach ($names as $n) {
            try {
                $r = Http::timeout(15)->get("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/$n.svg");
                if ($r->failed() || !str_contains($r->body(), '<svg')) throw new \RuntimeException('not found');
                $svg = trim(preg_replace('/\s+/', ' ', $r->body()));
                $svg = str_replace(['"', '<', '>', '#'], ["'", '%3C', '%3E', '%23'], $svg);
                $css .= ".bi-$n{--bi:url(\"data:image/svg+xml,$svg\")}\n";
                $ok++;
            } catch (\Throwable $e) {
                $bad[] = $n;
            }
        }
        if (!is_dir(public_path('vendor'))) mkdir(public_path('vendor'), 0755, true);
        file_put_contents(public_path('vendor/icons.css'), $css);
        $this->info("$ok icons written to public/vendor/icons.css (" . number_format(strlen($css) / 1024, 1) . ' KB).');
        if ($bad) $this->warn('Not found (probably not real icons, ignore): ' . implode(', ', $bad));
        $this->line('Run this command again whenever you start using a new icon on the public site.');
        return 0;
    }
}
