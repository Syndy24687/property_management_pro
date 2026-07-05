<?php

namespace App\Services;

use App\Models\MeterReading;
use App\Models\UtilityCharge;
use App\Models\UtilityMeter;
use App\Models\UtilityType;
use Illuminate\Support\Facades\DB;

class UtilityBillingService
{
    /**
     * Record a new meter reading and auto-calculate consumption.
     */
    public function recordReading(int $meterId, float $readingValue, ?int $readById = null, ?string $readingDate = null, ?string $photoPath = null): MeterReading
    {
        $meter = UtilityMeter::findOrFail($meterId);

        // Get previous reading value
        $lastReading = $meter->readings()->orderByDesc('reading_date')->first();
        $previousValue = $lastReading ? $lastReading->reading_value : 0;

        $reading = MeterReading::create([
            'utility_meter_id' => $meterId,
            'read_by'          => $readById ?? auth('api')->id(),
            'reading_date'     => $readingDate ?? now()->toDateString(),
            'reading_value'    => $readingValue,
            'previous_value'   => $previousValue,
            'photo_path'       => $photoPath,
            // usage is auto-calculated in model boot
        ]);

        return $reading->load('utilityMeter.utilityType');
    }

    /**
     * Generate a utility charge from a meter reading.
     */
    public function generateCharge(MeterReading $reading, ?float $customRate = null): UtilityCharge
    {
        $meter = $reading->utilityMeter;
        $utilityType = $meter->utilityType;

        // Use custom rate, or utility type default, or system setting
        $rate = $customRate ?? $utilityType->default_rate;

        // Determine billing period from reading dates
        $lastCharge = $meter->charges()->orderByDesc('billing_period_end')->first();
        $periodStart = $lastCharge ? $lastCharge->billing_period_end->addDay() : $reading->reading_date->startOfMonth();

        return UtilityCharge::create([
            'utility_meter_id'    => $meter->id,
            'meter_reading_id'    => $reading->id,
            'billing_period_start' => $periodStart->toDateString(),
            'billing_period_end'   => $reading->reading_date->toDateString(),
            'usage'               => $reading->usage,
            'rate'                => $rate,
            // amount is auto-calculated in model boot
            'status'              => 'pending',
        ]);
    }

    /**
     * Record a reading AND generate a charge in one step.
     */
    public function recordReadingAndCharge(int $meterId, float $readingValue, ?float $customRate = null): array
    {
        return DB::transaction(function () use ($meterId, $readingValue, $customRate) {
            $reading = $this->recordReading($meterId, $readingValue);
            $charge = $this->generateCharge($reading, $customRate);

            return [
                'reading' => $reading,
                'charge'  => $charge->load('utilityMeter.utilityType'),
            ];
        });
    }

    /**
     * Get pending (unbilled) utility charges for a unit.
     */
    public function getPendingCharges(int $unitId): \Illuminate\Database\Eloquent\Collection
    {
        return UtilityCharge::where('status', 'pending')
            ->whereHas('utilityMeter', fn($q) => $q->where('unit_id', $unitId))
            ->with('utilityMeter.utilityType')
            ->get();
    }

    /**
     * Get all utility types (tariffs).
     */
    public function getUtilityTypes()
    {
        return UtilityType::orderBy('name')->get();
    }

    /**
     * Create a utility type (tariff).
     */
    public function createUtilityType(array $data): UtilityType
    {
        return UtilityType::create($data);
    }

    /**
     * Update a utility type (tariff rate change).
     */
    public function updateUtilityType(int $id, array $data): UtilityType
    {
        $type = UtilityType::findOrFail($id);
        $type->update($data);
        return $type;
    }

    /**
     * Install a meter on a unit.
     */
    public function installMeter(array $data): UtilityMeter
    {
        return UtilityMeter::create($data);
    }

    /**
     * Get meters for a unit.
     */
    public function getMetersForUnit(int $unitId)
    {
        return UtilityMeter::where('unit_id', $unitId)
            ->with(['utilityType', 'latestReading'])
            ->get();
    }

    /**
     * Get reading history for a meter.
     */
    public function getReadings(int $meterId, int $perPage = 15)
    {
        return MeterReading::where('utility_meter_id', $meterId)
            ->with('readBy')
            ->orderByDesc('reading_date')
            ->paginate($perPage);
    }

    /**
     * Get utility charges with filters.
     */
    public function getCharges(array $filters = [], int $perPage = 15)
    {
        $query = UtilityCharge::with(['utilityMeter.utilityType', 'utilityMeter.unit']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['unit_id'])) {
            $query->whereHas('utilityMeter', fn($q) => $q->where('unit_id', $filters['unit_id']));
        }

        return $query->orderByDesc('billing_period_end')->paginate($perPage);
    }
}
