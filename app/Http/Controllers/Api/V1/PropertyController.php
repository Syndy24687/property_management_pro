<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\StorePropertyRequest;
use App\Http\Requests\Property\UpdatePropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\UnitResource;
use App\Services\PropertyService;
use App\Services\UnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PropertyController extends Controller
{
    public function __construct(
        protected PropertyService $propertyService,
        protected UnitService     $unitService
    ) {}

    /**
     * Display a paginated list of properties.
     *
     * GET /api/v1/properties
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['status', 'type', 'city', 'state', 'search']);
        $perPage = $request->integer('per_page', 15);

        $properties = $this->propertyService->getAllProperties($filters, $perPage);

        return PropertyResource::collection($properties);
    }

    /**
     * Store a newly created property.
     *
     * POST /api/v1/properties
     */
    public function store(StorePropertyRequest $request): JsonResponse
    {
        $property = $this->propertyService->createProperty($request->validated());

        return (new PropertyResource($property))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified property.
     *
     * GET /api/v1/properties/{id}
     */
    public function show(int $id): JsonResponse
    {
        $property = $this->propertyService->getProperty($id);

        if (!$property) {
            return response()->json(['message' => 'Property not found.'], 404);
        }

        return (new PropertyResource($property))->response();
    }

    /**
     * Update the specified property.
     *
     * PUT /api/v1/properties/{id}
     */
    public function update(UpdatePropertyRequest $request, int $id): JsonResponse
    {
        $property = $this->propertyService->updateProperty($id, $request->validated());

        return (new PropertyResource($property))->response();
    }

    /**
     * Remove the specified property (soft delete).
     *
     * DELETE /api/v1/properties/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->propertyService->deleteProperty($id);

        return response()->json(['message' => 'Property deleted successfully.'], 200);
    }

    /**
     * Get units for a specific property.
     *
     * GET /api/v1/properties/{id}/units
     */
    public function units(int $id): JsonResponse
    {
        $units = $this->unitService->getUnitsByProperty($id);

        return response()->json([
            'data' => UnitResource::collection($units),
        ]);
    }
}
