<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'school_owner_id',
        'subject',
        'message',
        'priority',
        'status',
        'assigned_admin_id',
        'internal_notes',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(SchoolOwner::class, 'school_owner_id');
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(PlatformAdmin::class, 'assigned_admin_id');
    }
}
