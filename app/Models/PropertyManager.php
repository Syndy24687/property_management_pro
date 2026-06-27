<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyManager extends Model
{
    protected $fillable = [
        'property_id',
        'user_id',
        'assigned_at',
        'removed_at',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'date',
            'removed_at'  => 'date',
            'is_primary'  => 'boolean',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('removed_at');
    }
}
