<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SystemSetting;
use App\Support\PublicDiskUrl;

class SchoolSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_name',
        'motto',
        'vision',
        'logo_path',
        'school_address',
        'school_phone',
        'school_email',
        'exam_duration',
        'pass_mark',
        'auto_grade',
    ];

    protected function casts(): array
    {
        return [
            'auto_grade' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return self::firstOrCreate([], [
            'school_name' => 'TestServes',
            'exam_duration' => 120,
            'pass_mark' => 50,
            'auto_grade' => true,
        ]);
    }

    public function getLogoUrlAttribute(): string
    {
        return PublicDiskUrl::make($this->logo_path, asset(SystemSetting::DEFAULT_PLATFORM_LOGO));
    }
}
