<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UtilityType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit_of_measure',
        'default_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_rate' => 'decimal:4',
            'is_active'    => 'boolean',
        ];
    }

    public function meters(): HasMany
    {
        return $this->hasMany(UtilityMeter::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
