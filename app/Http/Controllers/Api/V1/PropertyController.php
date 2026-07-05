<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\StorePropertyRequest;
use App\Http\Requests\Property\UpdatePropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\UnitResource;
use App\Models\Image;
use App\Models\Property;
use App\Services\ImageUploadService;
use App\Services\PropertyService;
use App\Services\UnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PropertyController extends Controller
{
    public function __construct(
        protected PropertyService    $propertyService,
        protected UnitService        $unitService,
        protected ImageUploadService $imageService
    ) {}

    /**
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
     * POST /api/v1/properties
     */
    public function store(StorePropertyRequest $request): JsonResponse
    {
        $property = $this->propertyService->createProperty($request->validated());

        // Handle image uploads
        if ($request->hasFile('images')) {
            $this->imageService->uploadMultiple($request->file('images'), $property);
        }

        return response()->json([
            'success' => true,
            'message' => 'Property created successfully.',
            'data'    => new PropertyResource($property->load('images')),
        ], 201);
    }

    /**
     * GET /api/v1/properties/{id}
     */
    public function show(int $id): JsonResponse
    {
        $property = $this->propertyService->getProperty($id);

        if (!$property) {
            return response()->json(['success' => false, 'message' => 'Property not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => new PropertyResource($property->load(['images', 'units', 'propertyManagers.user'])),
        ]);
    }

    /**
     * PUT /api/v1/properties/{id}
     */
    public function update(UpdatePropertyRequest $request, int $id): JsonResponse
    {
        $property = $this->propertyService->updateProperty($id, $request->validated());

        if ($request->hasFile('images')) {
            $this->imageService->uploadMultiple($request->file('images'), $property);
        }

        return response()->json([
            'success' => true,
            'message' => 'Property updated successfully.',
            'data'    => new PropertyResource($property->load('images')),
        ]);
    }

    /**
     * DELETE /api/v1/properties/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->propertyService->deleteProperty($id);

        return response()->json([
            'success' => true,
            'message' => 'Property deleted successfully.',
        ]);
    }

    /**
     * GET /api/v1/properties/{id}/units
     */
    public function units(int $id): JsonResponse
    {
        $units = $this->unitService->getUnitsByProperty($id);

        return response()->json([
            'success' => true,
            'data'    => UnitResource::collection($units),
        ]);
    }

    /**
     * POST /api/v1/properties/{id}/images
     */
    public function uploadImages(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'images'   => ['required', 'array', 'max:10'],
            'images.*' => ['required', 'image', 'max:5120'],
        ]);

        $property = Property::findOrFail($id);
        $images = $this->imageService->uploadMultiple($request->file('images'), $property);

        return response()->json([
            'success' => true,
            'message' => count($images) . ' image(s) uploaded successfully.',
            'data'    => collect($images)->map(fn($img) => [
                'id'         => $img->id,
                'url'        => $img->url,
                'file_name'  => $img->file_name,
                'is_primary' => $img->is_primary,
            ]),
        ], 201);
    }

    /**
     * DELETE /api/v1/properties/{propertyId}/images/{imageId}
     */
    public function deleteImage(int $propertyId, int $imageId): JsonResponse
    {
        $image = Image::where('id', $imageId)
            ->where('imageable_type', Property::class)
            ->where('imageable_id', $propertyId)
            ->firstOrFail();

        $this->imageService->delete($image);

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully.',
        ]);
    }
}
