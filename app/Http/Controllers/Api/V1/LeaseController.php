<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lease\StoreLeaseRequest;
use App\Http\Requests\Lease\UpdateLeaseRequest;
use App\Http\Resources\LeaseResource;
use App\Services\LeaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LeaseController extends Controller
{
    public function __construct(
        protected LeaseService $leaseService
    ) {}

    /**
     * Display a paginated list of leases.
     *
     * GET /api/v1/leases
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['status', 'tenant_id', 'unit_id']);
        $perPage = $request->integer('per_page', 15);

        $leases = $this->leaseService->getAllLeases($filters, $perPage);

        return LeaseResource::collection($leases);
    }

    /**
     * Store a newly created lease.
     *
     * POST /api/v1/leases
     */
    public function store(StoreLeaseRequest $request): JsonResponse
    {
        $lease = $this->leaseService->createLease($request->validated());

        return (new LeaseResource($lease))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified lease.
     *
     * GET /api/v1/leases/{id}
     */
    public function show(int $id): JsonResponse
    {
        $lease = $this->leaseService->getLease($id);

        if (!$lease) {
            return response()->json(['message' => 'Lease not found.'], 404);
        }

        return (new LeaseResource($lease))->response();
    }

    /**
     * Update the specified lease.
     *
     * PUT /api/v1/leases/{id}
     */
    public function update(UpdateLeaseRequest $request, int $id): JsonResponse
    {
        $lease = $this->leaseService->updateLease($id, $request->validated());

        return (new LeaseResource($lease))->response();
    }
}
