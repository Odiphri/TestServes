<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use App\Support\PublicDiskUrl;

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
        return PublicDiskUrl::make($this->profile_picture, asset('images/default-avatar.svg'));
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
            Storage::disk('public')->delete($this->profile_picture);
        }

        $path = $file->store('profile-photos', 'public');
        $this->profile_picture = $path;
        $this->save();
    }

    public function removeProfilePicture(): void
    {
        if ($this->profile_picture) {
            Storage::disk('public')->delete($this->profile_picture);
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
