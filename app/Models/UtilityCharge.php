<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UtilityCharge extends Model
{
    protected $fillable = [
        'utility_meter_id',
        'meter_reading_id',
        'billing_period_start',
        'billing_period_end',
        'usage',
        'rate',
        'amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'billing_period_start' => 'date',
            'billing_period_end'   => 'date',
            'usage'  => 'decimal:2',
            'rate'   => 'decimal:4',
            'amount' => 'decimal:2',
        ];
    }

    public function utilityMeter(): BelongsTo
    {
        return $this->belongsTo(UtilityMeter::class);
    }

    public function meterReading(): BelongsTo
    {
        return $this->belongsTo(MeterReading::class);
    }

    public function invoiceItem(): HasOne
    {
        return $this->hasOne(InvoiceItem::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // ─── Boot ───────────────────────────────────────────────────────

    protected static function booted(): void
    {
        // Auto-calculate amount = usage × rate
        static::saving(function (UtilityCharge $charge) {
            $charge->amount = $charge->usage * $charge->rate;
        });
    }
}
