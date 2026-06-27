<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\MaintenanceRequest\StoreMaintenanceRequestRequest;
use App\Http\Requests\MaintenanceRequest\UpdateMaintenanceRequestRequest;
use App\Http\Resources\MaintenanceRequestResource;
use App\Services\MaintenanceRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MaintenanceRequestController extends Controller
{
    public function __construct(
        protected MaintenanceRequestService $maintenanceService
    ) {}

    /**
     * Display a paginated list of maintenance requests.
     *
     * GET /api/v1/maintenance-requests
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['status', 'priority', 'unit_id', 'tenant_id']);
        $perPage = $request->integer('per_page', 15);

        $requests = $this->maintenanceService->getAllRequests($filters, $perPage);

        return MaintenanceRequestResource::collection($requests);
    }

    /**
     * Store a newly created maintenance request.
     *
     * POST /api/v1/maintenance-requests
     */
    public function store(StoreMaintenanceRequestRequest $request): JsonResponse
    {
        $maintenanceRequest = $this->maintenanceService->createRequest($request->validated());

        return (new MaintenanceRequestResource($maintenanceRequest))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified maintenance request.
     *
     * GET /api/v1/maintenance-requests/{id}
     */
    public function show(int $id): JsonResponse
    {
        $maintenanceRequest = $this->maintenanceService->getRequest($id);

        if (!$maintenanceRequest) {
            return response()->json(['message' => 'Maintenance request not found.'], 404);
        }

        return (new MaintenanceRequestResource($maintenanceRequest))->response();
    }

    /**
     * Update the specified maintenance request.
     *
     * PUT /api/v1/maintenance-requests/{id}
     */
    public function update(UpdateMaintenanceRequestRequest $request, int $id): JsonResponse
    {
        $maintenanceRequest = $this->maintenanceService->updateRequest($id, $request->validated());

        return (new MaintenanceRequestResource($maintenanceRequest))->response();
    }
}
