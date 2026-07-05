<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'property_id',
        'unit_number',
        'floor',
        'bedrooms',
        'bathrooms',
        'area_sqft',
        'rent_amount',
        'deposit_amount',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'rent_amount'    => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'area_sqft'      => 'decimal:2',
            'bedrooms'       => 'integer',
            'bathrooms'      => 'integer',
            'floor'          => 'integer',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────────

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    /**
     * Utility meters installed in this unit.
     */
    public function utilityMeters(): HasMany
    {
        return $this->hasMany(UtilityMeter::class);
    }

    /**
     * Documents attached to this unit (polymorphic).
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Images attached to this unit (polymorphic).
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable')->orderBy('sort_order');
    }

    // ─── Scopes ─────────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    // ─── Helpers ────────────────────────────────────────────────────

    public function hasActiveLease(): bool
    {
        return $this->leases()->where('status', 'active')->exists();
    }
}
