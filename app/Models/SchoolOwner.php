<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class SchoolOwner extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'school_id',
        'name',
        'email',
        'phone',
        'profile_picture',
        'password',
        'is_primary',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_primary' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function getProfilePictureUrlAttribute(): ?string
    {
        if (blank($this->profile_picture)) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($this->profile_picture)) {
            return null;
        }

        $path = str_replace('\\', '/', ltrim($this->profile_picture, '/'));

        return '/storage/'.$path.'?v='.$disk->lastModified($this->profile_picture);
    }

    public function payments()
    {
        return $this->hasMany(PaymentRecord::class);
    }
}
