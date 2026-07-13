<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'version',
        'content',
        'effective_at',
        'published_at',
        'is_published',
        'created_by_admin_id',
        'updated_by_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'effective_at' => 'datetime',
            'published_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->whereNotNull('published_at');
    }

    public static function currentVersion(string $slug): string
    {
        return static::published()
            ->where('slug', $slug)
            ->latest('published_at')
            ->value('version') ?? '1.0';
    }
}
