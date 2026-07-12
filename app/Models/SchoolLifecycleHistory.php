<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolLifecycleHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'previous_status',
        'new_status',
        'changed_by_admin_id',
        'changed_by_role',
        'reason',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(PlatformAdmin::class, 'changed_by_admin_id');
    }
}
