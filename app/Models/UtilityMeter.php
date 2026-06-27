<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UtilityMeter extends Model
{
    protected $fillable = [
        'unit_id',
        'utility_type_id',
        'meter_number',
        'installation_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'installation_date' => 'date',
            'is_active'         => 'boolean',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function utilityType(): BelongsTo
    {
        return $this->belongsTo(UtilityType::class);
    }

    public function readings(): HasMany
    {
        return $this->hasMany(MeterReading::class, 'utility_meter_id');
    }

    public function charges(): HasMany
    {
        return $this->hasMany(UtilityCharge::class, 'utility_meter_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the latest reading for this meter.
     */
    public function latestReading()
    {
        return $this->hasOne(MeterReading::class, 'utility_meter_id')->latestOfMany('reading_date');
    }
}
