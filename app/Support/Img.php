<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

class Img
{
    public static function store(UploadedFile $f, int $maxW = 1600): string
    {
        $dir = storage_path('app/public/uploads');
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        if (!function_exists('imagewebp') || !($src = @imagecreatefromstring(file_get_contents($f->getRealPath())))) return 'storage/' . $f->store('uploads', 'public');
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
        imagewebp($src, "$dir/$name.webp", 82);
        imagedestroy($src);
        return "storage/uploads/$name.webp";
    }
}
