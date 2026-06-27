<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeterReading extends Model
{
    protected $fillable = [
        'utility_meter_id',
        'read_by',
        'reading_date',
        'reading_value',
        'previous_value',
        'usage',
        'photo_path',
    ];

    protected function casts(): array
    {
        return [
            'reading_date'  => 'date',
            'reading_value' => 'decimal:2',
            'previous_value' => 'decimal:2',
            'usage'         => 'decimal:2',
        ];
    }

    public function utilityMeter(): BelongsTo
    {
        return $this->belongsTo(UtilityMeter::class);
    }

    public function readBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'read_by');
    }

    // ─── Boot ───────────────────────────────────────────────────────

    protected static function booted(): void
    {
        // Auto-calculate usage = reading_value - previous_value
        static::saving(function (MeterReading $reading) {
            $reading->usage = $reading->reading_value - $reading->previous_value;
        });
    }
}
