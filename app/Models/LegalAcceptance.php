<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LegalAcceptance extends Model
{
    use HasFactory;

    protected $fillable = [
        'acceptor_type',
        'acceptor_id',
        'privacy_policy_version',
        'terms_version',
        'accepted_at',
        'ip_address',
        'user_agent',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
        ];
    }

    public function acceptor(): MorphTo
    {
        return $this->morphTo();
    }
}
