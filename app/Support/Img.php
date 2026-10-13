<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

class Img
{
    const W = [240, 480, 800, 1200];
    public static function store(UploadedFile $f, int $maxW = 1600): string
    {
        $dir = storage_path('app/public/uploads');
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        if (!function_exists('imagewebp') || !($src = @imagecreatefromstring(file_get_contents($f->getRealPath())))) return 'storage/' . $f->store('uploads', 'public');
        @set_time_limit(120);
        if (function_exists('exif_read_data') && $f->getMimeType() === 'image/jpeg') {
            $o = (int) (@exif_read_data($f->getRealPath())['Orientation'] ?? 1);
            $src = match ($o) {
                3 => imagerotate($src, 180, 0),
                6 => imagerotate($src, -90, 0),
                8 => imagerotate($src, 90, 0),
                default => $src
            };
        }
        if (imagesx($src) > $maxW) $src = imagescale($src, $maxW);
        imagepalettetotruecolor($src);
        imagealphablending($src, true);
        imagesavealpha($src, true);
        $name = bin2hex(random_bytes(8));
        self::write($src, "$dir/$name");
        // JPEG copy for social share previews (some platforms do not show WebP)
        $bg = imagecreatetruecolor(imagesx($src), imagesy($src));
        imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
        imagecopy($bg, $src, 0, 0, 0, 0, imagesx($src), imagesy($src));
        imagejpeg($bg, "$dir/$name.jpg", 85);
        imagedestroy($bg);
        imagedestroy($src);
        return "storage/uploads/$name.webp";
    }
    // writes base.webp (+ .avif) and smaller sizes base-480.webp (+ .avif)
    public static function write($src, string $base, bool $main = true): void
    {
        $w0 = imagesx($src);
        if ($main) self::enc($src, $base);
        elseif (function_exists('imageavif') && !is_file("$base.avif")) self::enc($src, $base, true);
        foreach (self::W as $w) {
            if ($w >= $w0 || is_file("$base-$w.webp")) continue;
            $s = imagescale($src, $w);
            if ($s) {
                self::enc($s, "$base-$w");
                imagedestroy($s);
            }
        }
    }
    private static function enc($im, string $base, bool $avifOnly = false): void
    {
        imagesavealpha($im, true);
        if (!$avifOnly) imagewebp($im, "$base.webp", 80);
        if (function_exists('imageavif')) @imageavif($im, "$base.avif", 50, 8);
    }
}
