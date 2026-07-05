<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UtilityMeter;
use App\Services\UtilityBillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UtilityController extends Controller
{
    public function __construct(
        protected UtilityBillingService $utilityService
    ) {}

    // ─── Utility Types (Tariffs) ────────────────────────────────

    /**
     * GET /api/v1/utility-types
     */
    public function indexTypes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->utilityService->getUtilityTypes(),
        ]);
    }

    /**
     * POST /api/v1/utility-types
     */
    public function storeType(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'unit_of_measure' => ['required', 'string', 'max:50'],
            'default_rate'    => ['required', 'numeric', 'min:0'],
            'is_active'       => ['sometimes', 'boolean'],
        ]);

        $type = $this->utilityService->createUtilityType($validated);

        return response()->json([
            'success' => true,
            'message' => 'Utility type created.',
            'data'    => $type,
        ], 201);
    }

    /**
     * PUT /api/v1/utility-types/{id}
     */
    public function updateType(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name'            => ['sometimes', 'string', 'max:255'],
            'unit_of_measure' => ['sometimes', 'string', 'max:50'],
            'default_rate'    => ['sometimes', 'numeric', 'min:0'],
            'is_active'       => ['sometimes', 'boolean'],
        ]);

        $type = $this->utilityService->updateUtilityType($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Utility type updated.',
            'data'    => $type,
        ]);
    }

    // ─── Utility Meters ─────────────────────────────────────────

    /**
     * GET /api/v1/units/{unitId}/meters
     */
    public function indexMeters(int $unitId): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->utilityService->getMetersForUnit($unitId),
        ]);
    }

    /**
     * POST /api/v1/units/{unitId}/meters
     */
    public function storeMeter(Request $request, int $unitId): JsonResponse
    {
        $validated = $request->validate([
            'utility_type_id'   => ['required', 'exists:utility_types,id'],
            'meter_number'      => ['required', 'string', 'max:100'],
            'installation_date' => ['nullable', 'date'],
        ]);

        $validated['unit_id'] = $unitId;
        $meter = $this->utilityService->installMeter($validated);

        return response()->json([
            'success' => true,
            'message' => 'Meter installed.',
            'data'    => $meter->load('utilityType'),
        ], 201);
    }

    /**
     * PUT /api/v1/meters/{id}
     */
    public function updateMeter(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'meter_number' => ['sometimes', 'string', 'max:100'],
            'is_active'    => ['sometimes', 'boolean'],
        ]);

        $meter = UtilityMeter::findOrFail($id);
        $meter->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Meter updated.',
            'data'    => $meter,
        ]);
    }

    /**
     * DELETE /api/v1/meters/{id}
     */
    public function destroyMeter(int $id): JsonResponse
    {
        $meter = UtilityMeter::findOrFail($id);
        $meter->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Meter decommissioned.',
        ]);
    }

    // ─── Meter Readings ─────────────────────────────────────────

    /**
     * GET /api/v1/meters/{meterId}/readings
     */
    public function indexReadings(Request $request, int $meterId): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);

        return response()->json([
            'success' => true,
            'data'    => $this->utilityService->getReadings($meterId, $perPage),
        ]);
    }

    /**
     * POST /api/v1/meters/{meterId}/readings
     * Records reading AND generates a utility charge automatically.
     */
    public function storeReading(Request $request, int $meterId): JsonResponse
    {
        $validated = $request->validate([
            'reading_value' => ['required', 'numeric', 'min:0'],
            'reading_date'  => ['sometimes', 'date'],
            'custom_rate'   => ['sometimes', 'numeric', 'min:0'],
        ]);

        $result = $this->utilityService->recordReadingAndCharge(
            $meterId,
            $validated['reading_value'],
            $validated['custom_rate'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Reading recorded and charge generated.',
            'data'    => $result,
        ], 201);
    }

    // ─── Utility Charges ────────────────────────────────────────

    /**
     * GET /api/v1/utility-charges
     */
    public function indexCharges(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'unit_id']);
        $perPage = $request->integer('per_page', 15);

        return response()->json([
            'success' => true,
            'data'    => $this->utilityService->getCharges($filters, $perPage),
        ]);
    }
}
