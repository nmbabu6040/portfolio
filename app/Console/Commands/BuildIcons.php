<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, File, Http};

class BuildIcons extends Command
{
    protected $signature = 'assets:icons';
    protected $description = 'Build a small icon stylesheet with only the Bootstrap icons the public site uses';
    const EXTRA = ['linkedin', 'github', 'facebook', 'twitter-x', 'telegram', 'whatsapp', 'youtube', 'instagram', 'arrow-up', 'arrow-left', 'arrow-right', 'arrow-up-right', 'chevron-left', 'chevron-right', 'x-lg', 'list', 'quote', 'heart', 'heart-fill', 'circle-half', 'check-circle-fill', 'envelope', 'telephone', 'chat-dots', 'download', 'send', 'calendar-check', 'receipt', 'link-45deg', 'box-arrow-up', 'play-circle-fill', 'journal-text', 'tools', 'info-circle', 'trophy', 'share', 'mortarboard', 'patch-check', 'award'];

    public function handle(): int
    {
        config(['session.driver' => 'array']);
        $pat = '/\bbi-([a-z0-9]+(?:-[a-z0-9]+)*)/';
        $names = collect(self::EXTRA);

        // 1) icon names written in the code
        foreach ([app_path(), resource_path('views'), config_path(), database_path('seeders'), public_path('js')] as $dir) {
            if (!is_dir($dir)) continue;
            foreach (File::allFiles($dir) as $f) {
                $p = str_replace('\\', '/', $f->getPathname());
                if (!preg_match('/\.(php|js)$/', $p) || str_contains($p, '/views/admin/') || str_ends_with($p, '/admin.js') || str_ends_with($p, 'BuildIcons.php')) continue;
                if (preg_match_all($pat, (string) file_get_contents($f->getPathname()), $m)) $names = $names->merge($m[1]);
            }
        }

        // 2) icon names in the real pages, so icons that are chosen while the page is built are not missed
        $urls = ['/', '/about', '/experience', '/technology', '/projects', '/services', '/contact', '/quote', '/blog', '/privacy', '/affiliate-disclosure', '/resume'];
        try {
            foreach (DB::table('services')->pluck('slug') as $s) $urls[] = "/services/$s";
            foreach (DB::table('projects')->pluck('id') as $i) $urls[] = "/projects/$i";
            foreach (DB::table('posts')->where('published', 1)->pluck('slug') as $s) $urls[] = "/blog/$s";
            foreach (DB::table('categories')->pluck('slug') as $s) $urls[] = "/blog/category/$s";
            foreach (DB::table('services')->pluck('icon') as $i) if ($i && preg_match('/^bi-([a-z0-9-]+)$/', $i, $m)) $names->push($m[1]);
        } catch (\Throwable $e) {
        }
        if (!empty(Setting::map()['maintenance'])) $this->warn('Maintenance mode is ON, so the pages cannot be read. Turn it off and run this again.');
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $read = 0;
        foreach ($urls as $u) {
            try {
                $res = $kernel->handle(Request::create($u, 'GET'));
                if ($res->getStatusCode() === 200 && preg_match_all($pat, (string) $res->getContent(), $m)) {
                    $names = $names->merge($m[1]);
                    $read++;
                }
            } catch (\Throwable $e) {
            }
        }
        $this->line("$read of " . count($urls) . ' pages were read.');

        // 3) download every icon, with retries and a second server
        $names = $names->filter(fn($n) => strlen($n) > 1)->unique()->sort()->values();
        $css = ".ic-s .bi{display:inline-block;line-height:1;vertical-align:-.125em}\n"
            . ".ic-s .bi::before{content:\"\";display:block;width:1em;height:1em;background-color:currentColor;-webkit-mask:var(--bi) center/contain no-repeat;mask:var(--bi) center/contain no-repeat}\n";
        $ok = 0;
        $fake = [];
        $failed = [];
        foreach ($names as $n) {
            [$svg, $notReal] = $this->svg($n);
            if ($svg) {
                $d = str_replace(['"', '<', '>', '#'], ["'", '%3C', '%3E', '%23'], trim(preg_replace('/\s+/', ' ', $svg)));
                $css .= ".ic-s .bi-$n{--bi:url(\"data:image/svg+xml,$d\")}\n";
                $ok++;
            } elseif ($notReal) $fake[] = $n;
            else $failed[] = $n;
        }

        $path = public_path('vendor/icons.css');
        if ($failed) {
            if (is_file($path)) unlink($path);
            $this->error('Some icons could not be downloaded (network problem): ' . implode(', ', $failed));
            $this->warn('The small icon file was NOT created, so the site keeps using the full icon font and no icon is lost. Check your internet and run this command again.');
            return 1;
        }
        if (!is_dir(dirname($path))) mkdir(dirname($path), 0755, true);
        file_put_contents($path, $css);
        $this->info("$ok icons written to public/vendor/icons.css (" . number_format(strlen($css) / 1024, 1) . ' KB).');
        if ($fake) $this->line('Not real icon names (a typo or just text, safe to ignore): ' . implode(', ', $fake));
        $this->line('Run this command again after you start using a new icon on the public site.');
        return 0;
    }
    private function svg(string $n): array
    {
        $notFound = 0;
        foreach (["https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/$n.svg", "https://unpkg.com/bootstrap-icons@1.11.3/icons/$n.svg"] as $url) {
            for ($t = 0; $t < 3; $t++) {
                try {
                    $r = Http::timeout(20)->get($url);
                } catch (\Throwable $e) {
                    usleep(400000);
                    continue;
                }
                if ($r->status() === 404) {
                    $notFound++;
                    break;
                }
                if ($r->ok() && str_contains($r->body(), '<svg')) return [$r->body(), false];
                usleep(400000);
            }
        }
        return [null, $notFound >= 2];
    }
}
