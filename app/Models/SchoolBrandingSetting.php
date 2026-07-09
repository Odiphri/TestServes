<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolBrandingSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'primary_color',
        'secondary_color',
        'accent_color',
        'logo_path',
        'short_name',
        'portal_display_name',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
