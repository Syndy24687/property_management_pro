<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TenantResource;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TenantController extends Controller
{
    public function __construct(
        protected TenantService $tenantService
    ) {}

    /**
     * Display a paginated list of tenants.
     *
     * GET /api/v1/tenants
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['status', 'search']);
        $perPage = $request->integer('per_page', 15);

        $tenants = $this->tenantService->getAllTenants($filters, $perPage);

        return TenantResource::collection($tenants);
    }

    /**
     * Display the specified tenant.
     *
     * GET /api/v1/tenants/{id}
     */
    public function show(int $id): JsonResponse
    {
        $tenant = $this->tenantService->getTenant($id);

        if (!$tenant) {
            return response()->json(['message' => 'Tenant not found.'], 404);
        }

        return (new TenantResource($tenant))->response();
    }
}
