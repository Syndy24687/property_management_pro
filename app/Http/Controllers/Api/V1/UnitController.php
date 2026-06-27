<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Unit\StoreUnitRequest;
use App\Http\Requests\Unit\UpdateUnitRequest;
use App\Http\Resources\UnitResource;
use App\Services\UnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UnitController extends Controller
{
    public function __construct(
        protected UnitService $unitService
    ) {}

    /**
     * Display a paginated list of units.
     *
     * GET /api/v1/units
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['property_id', 'status', 'min_rent', 'max_rent', 'bedrooms']);
        $perPage = $request->integer('per_page', 15);

        $units = $this->unitService->getAllUnits($filters, $perPage);

        return UnitResource::collection($units);
    }

    /**
     * Store a newly created unit.
     *
     * POST /api/v1/units
     */
    public function store(StoreUnitRequest $request): JsonResponse
    {
        $unit = $this->unitService->createUnit($request->validated());

        return (new UnitResource($unit))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified unit.
     *
     * GET /api/v1/units/{id}
     */
    public function show(int $id): JsonResponse
    {
        $unit = $this->unitService->getUnit($id);

        if (!$unit) {
            return response()->json(['message' => 'Unit not found.'], 404);
        }

        return (new UnitResource($unit))->response();
    }

    /**
     * Update the specified unit.
     *
     * PUT /api/v1/units/{id}
     */
    public function update(UpdateUnitRequest $request, int $id): JsonResponse
    {
        $unit = $this->unitService->updateUnit($id, $request->validated());

        return (new UnitResource($unit))->response();
    }

    /**
     * Remove the specified unit (soft delete).
     *
     * DELETE /api/v1/units/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->unitService->deleteUnit($id);

        return response()->json(['message' => 'Unit deleted successfully.'], 200);
    }
}
