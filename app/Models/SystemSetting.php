<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use App\Support\PublicDiskUrl;

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

        return PublicDiskUrl::make($path, asset(static::DEFAULT_PLATFORM_LOGO));
    }
}
