<?php

namespace App\Console\Commands;

use App\Support\Ui;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class VendorAssets extends Command
{
    protected $signature = 'assets:vendor';
    protected $description = 'Download Bootstrap, icons, fonts, editor and tech logos to public/vendor so the site does not depend on a CDN';
    public function handle(): int
    {
        $cdn = 'https://cdn.jsdelivr.net/npm/';
        $files = [
            'bootstrap/bootstrap.min.css' => $cdn . 'bootstrap@5.3.3/dist/css/bootstrap.min.css',
            'bootstrap/bootstrap.bundle.min.js' => $cdn . 'bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
            'bootstrap-icons/font/bootstrap-icons.min.css' => $cdn . 'bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
            'bootstrap-icons/font/fonts/bootstrap-icons.woff2' => $cdn . 'bootstrap-icons@1.11.3/font/fonts/bootstrap-icons.woff2',
            'bootstrap-icons/font/fonts/bootstrap-icons.woff' => $cdn . 'bootstrap-icons@1.11.3/font/fonts/bootstrap-icons.woff',
            'quill/quill.snow.css' => $cdn . 'quill@2.0.3/dist/quill.snow.css',
            'quill/quill.js' => $cdn . 'quill@2.0.3/dist/quill.js',
            'sortable/Sortable.min.js' => $cdn . 'sortablejs@1.15.2/Sortable.min.js',
            'fonts/inter.woff2' => $cdn . '@fontsource-variable/inter@5/files/inter-latin-wght-normal.woff2',
            'fonts/plus-jakarta-sans.woff2' => $cdn . '@fontsource-variable/plus-jakarta-sans@5/files/plus-jakarta-sans-latin-wght-normal.woff2',
        ];
        foreach (array_unique(array_values(Ui::DEV)) as $k) {
            $files["devicon/$k.svg"] = "https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/$k/$k-original.svg";
        }
        $ok = 0;
        $bad = [];
        foreach ($files as $rel => $url) {
            try {
                $r = Http::timeout(40)->get($url);
                if ($r->failed() || strlen($r->body()) < 100) throw new \RuntimeException('HTTP ' . $r->status());
                $path = public_path("vendor/$rel");
                if (!is_dir(dirname($path))) mkdir(dirname($path), 0755, true);
                file_put_contents($path, $r->body());
                $ok++;
            } catch (\Throwable $e) {
                $bad[] = $rel;
            }
        }
        if (is_file(public_path('vendor/fonts/inter.woff2')) && is_file(public_path('vendor/fonts/plus-jakarta-sans.woff2'))) {
            file_put_contents(
                public_path('vendor/fonts.css'),
                "@font-face{font-family:Inter;font-style:normal;font-display:swap;font-weight:100 900;src:url(fonts/inter.woff2) format('woff2')}\n"
                    . "@font-face{font-family:'Plus Jakarta Sans';font-style:normal;font-display:swap;font-weight:200 800;src:url(fonts/plus-jakarta-sans.woff2) format('woff2')}\n"
            );
        }
        $this->info("$ok of " . count($files) . ' files downloaded.');
        if ($bad) $this->warn('Could not download (the site will keep using the CDN for these): ' . implode(', ', $bad));
        return 0;
    }
}
