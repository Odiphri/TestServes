<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_picture',
        'bio',
        'phone',
        'address',
        'date_of_birth',
        'age',
        'gender',
        'complexion',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getProfilePictureUrlAttribute(): string
    {
        if ($this->profile_picture) {
            return Storage::url($this->profile_picture);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->user->full_name) . '&color=7F9CF5&background=EBF4FF';
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : $this->attributes['age'] ?? null;
    }

    public function getFormattedAddressAttribute(): string
    {
        return $this->address ?? 'Not provided';
    }

    public function getFormattedPhoneAttribute(): string
    {
        return $this->phone ?? 'Not provided';
    }

    public function getFormattedGenderAttribute(): string
    {
        return $this->gender ? ucfirst($this->gender) : 'Not specified';
    }

    public function updateProfilePicture($file): void
    {
        if ($this->profile_picture) {
            Storage::delete($this->profile_picture);
        }

        $path = $file->store('profile-pictures', 'public');
        $this->profile_picture = $path;
        $this->save();
    }

    public function removeProfilePicture(): void
    {
        if ($this->profile_picture) {
            Storage::delete($this->profile_picture);
            $this->profile_picture = null;
            $this->save();
        }
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    public function scopeWithProfilePicture($query)
    {
        return $query->whereNotNull('profile_picture');
    }
}
