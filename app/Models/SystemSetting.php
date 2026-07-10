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

    public const DEFAULT_PLATFORM_LOGO = 'images/tslogo.jpeg';

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
            return asset(static::DEFAULT_PLATFORM_LOGO);
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return asset(static::DEFAULT_PLATFORM_LOGO);
        }

        $version = $disk->lastModified($path);
        $publicPath = str_replace('\\', '/', ltrim($path, '/'));

        return '/storage/'.$publicPath.'?v='.$version;
    }
}
