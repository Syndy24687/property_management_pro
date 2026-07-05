<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Unit\StoreUnitRequest;
use App\Http\Requests\Unit\UpdateUnitRequest;
use App\Http\Resources\UnitResource;
use App\Models\Image;
use App\Models\Unit;
use App\Services\ImageUploadService;
use App\Services\UnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UnitController extends Controller
{
    public function __construct(
        protected UnitService        $unitService,
        protected ImageUploadService $imageService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['status', 'property_id', 'min_rent', 'max_rent', 'bedrooms']);
        return UnitResource::collection($this->unitService->getAllUnits($filters, $request->integer('per_page', 15)));
    }

    public function store(StoreUnitRequest $request): JsonResponse
    {
        $unit = $this->unitService->createUnit($request->validated());

        if ($request->hasFile('images')) {
            $this->imageService->uploadMultiple($request->file('images'), $unit);
        }

        return response()->json([
            'success' => true,
            'message' => 'Unit created successfully.',
            'data'    => new UnitResource($unit->load('images')),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $unit = $this->unitService->getUnit($id);

        if (!$unit) {
            return response()->json(['success' => false, 'message' => 'Unit not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => new UnitResource($unit->load(['property', 'images', 'leases.tenant'])),
        ]);
    }

    public function update(UpdateUnitRequest $request, int $id): JsonResponse
    {
        $unit = $this->unitService->updateUnit($id, $request->validated());

        if ($request->hasFile('images')) {
            $this->imageService->uploadMultiple($request->file('images'), $unit);
        }

        return response()->json([
            'success' => true,
            'message' => 'Unit updated successfully.',
            'data'    => new UnitResource($unit->load('images')),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->unitService->deleteUnit($id);

        return response()->json([
            'success' => true,
            'message' => 'Unit deleted successfully.',
        ]);
    }

    /**
     * POST /api/v1/units/{id}/images
     */
    public function uploadImages(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'images'   => ['required', 'array', 'max:10'],
            'images.*' => ['required', 'image', 'max:5120'],
        ]);

        $unit = Unit::findOrFail($id);
        $images = $this->imageService->uploadMultiple($request->file('images'), $unit);

        return response()->json([
            'success' => true,
            'message' => count($images) . ' image(s) uploaded.',
            'data'    => collect($images)->map(fn($img) => [
                'id'         => $img->id,
                'url'        => $img->url,
                'file_name'  => $img->file_name,
                'is_primary' => $img->is_primary,
            ]),
        ], 201);
    }

    /**
     * DELETE /api/v1/units/{unitId}/images/{imageId}
     */
    public function deleteImage(int $unitId, int $imageId): JsonResponse
    {
        $image = Image::where('id', $imageId)
            ->where('imageable_type', Unit::class)
            ->where('imageable_id', $unitId)
            ->firstOrFail();

        $this->imageService->delete($image);

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully.',
        ]);
    }
}
