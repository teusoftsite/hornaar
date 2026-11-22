<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Slaat foto's op: verkleind tot 1600 px, opnieuw gecodeerd (EXIF weg). */
class PhotoStore
{
    public const MAX = 1600;

    public function store(UploadedFile $file, string $folder): string
    {
        $path = $folder . '/' . now()->format('Y/m') . '/' . Str::uuid() . '.jpg';
        $data = $this->process($file->getRealPath());
        Storage::disk('local')->put($path, $data);
        return $path;
    }

    public function process(string $source): string
    {
        $raw = file_get_contents($source);
        $img = @imagecreatefromstring($raw);
        if (! $img) {
            throw new \InvalidArgumentException('Geen geldige foto.');
        }

        // Draaiing uit EXIF toepassen vóór het strippen
        if (function_exists('exif_read_data') && str_starts_with($raw, "\xFF\xD8")) {
            $exif = @exif_read_data($source);
            $img = match ((int) ($exif['Orientation'] ?? 1)) {
                3 => imagerotate($img, 180, 0),
                6 => imagerotate($img, -90, 0),
                8 => imagerotate($img, 90, 0),
                default => $img,
            };
        }

        $w = imagesx($img);
        $h = imagesy($img);
        $scale = min(1, self::MAX / max($w, $h));
        if ($scale < 1) {
            $nw = (int) round($w * $scale);
            $nh = (int) round($h * $scale);
            $dst = imagecreatetruecolor($nw, $nh);
            imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
            $img = $dst;
        }

        ob_start();
        imagejpeg($img, null, 82); // nieuwe JPEG zonder metadata
        return ob_get_clean();
    }
}
