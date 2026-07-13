<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactInquiry extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'General enquiry',
        'Technical support',
        'School onboarding',
        'Payment issue',
        'Partnership',
        'Privacy or data request',
        'Report a problem',
    ];

    public const STATUSES = ['new', 'assigned', 'in_progress', 'responded', 'closed', 'spam'];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'school_name',
        'category',
        'subject',
        'message',
        'status',
        'assigned_admin_id',
        'source',
        'ip_address',
        'user_agent',
        'submitted_at',
        'responded_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'responded_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(PlatformAdmin::class, 'assigned_admin_id');
    }
}
