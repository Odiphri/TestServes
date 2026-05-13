<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'amount',
        'fee_type',
        'applies_to_all_classes',
        'created_by',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'applies_to_all_classes' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'fee_item_school_class');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function exemptions(): HasMany
    {
        return $this->hasMany(StudentFeeExemption::class);
    }

    public function isOptional(): bool
    {
        return $this->fee_type === 'optional';
    }

    public function appliesToStudent(User $student): bool
    {
        if ($this->applies_to_all_classes) {
            return true;
        }

        return $student->school_class_id
            && $this->classes->contains('id', $student->school_class_id);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
