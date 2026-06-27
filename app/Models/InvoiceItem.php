<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'type',
        'description',
        'quantity',
        'unit_price',
        'amount',
        'utility_charge_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity'   => 'decimal:2',
            'unit_price' => 'decimal:2',
            'amount'     => 'decimal:2',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────────

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function utilityCharge(): BelongsTo
    {
        return $this->belongsTo(UtilityCharge::class);
    }

    // ─── Boot ───────────────────────────────────────────────────────

    protected static function booted(): void
    {
        // Auto-calculate amount = quantity × unit_price
        static::saving(function (InvoiceItem $item) {
            $item->amount = $item->quantity * $item->unit_price;
        });
    }
}
