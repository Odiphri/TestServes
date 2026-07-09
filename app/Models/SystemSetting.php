<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    public static function values(): array
    {
        if (! Schema::hasTable('system_settings')) {
            return [];
        }

        return static::pluck('value', 'key')->all();
    }

    public static function platformName(): string
    {
        return static::values()['platform_name'] ?? 'TestServes';
    }

    public static function platformLogoUrl(): ?string
    {
        $path = static::values()['platform_logo'] ?? null;

        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return null;
        }

        $version = $disk->lastModified($path);
        $publicPath = str_replace('\\', '/', ltrim($path, '/'));

        return '/storage/'.$publicPath.'?v='.$version;
    }
}
