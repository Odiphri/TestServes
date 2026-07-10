<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicDiskUrl
{
    public static function make(?string $path, ?string $fallback = null): ?string
    {
        if (blank($path)) {
            return $fallback;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return $fallback;
        }

        $publicPath = str_replace('\\', '/', ltrim($path, '/'));

        return asset('storage/'.$publicPath).'?v='.$disk->lastModified($path);
    }
}
