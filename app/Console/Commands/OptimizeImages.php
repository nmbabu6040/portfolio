<?php

namespace App\Console\Commands;

use App\Support\Img;
use Illuminate\Console\Command;

class OptimizeImages extends Command
{
    protected $signature = 'images:optimize';
    protected $description = 'Create responsive sizes and AVIF copies for images already uploaded';
    public function handle(): int
    {
        if (!function_exists('imagecreatefromwebp')) {
            $this->error('PHP GD with WebP support is required.');
            return 1;
        }
        @set_time_limit(0);
        $n = 0;
        foreach (glob(storage_path('app/public/uploads/*.webp')) ?: [] as $f) {
            if (preg_match('/-\d+\.webp$/', $f)) continue;
            $im = @imagecreatefromwebp($f);
            if (!$im) continue;
            Img::write($im, substr($f, 0, -5), false);
            imagedestroy($im);
            $n++;
            $this->line('done: ' . basename($f));
        }
        $this->info("$n image(s) processed." . (function_exists('imageavif') ? '' : ' AVIF is not supported by this PHP, so only WebP sizes were created.'));
        return 0;
    }
}
