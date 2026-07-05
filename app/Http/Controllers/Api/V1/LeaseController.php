<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lease\StoreLeaseRequest;
use App\Http\Requests\Lease\UpdateLeaseRequest;
use App\Http\Resources\LeaseResource;
use App\Services\InvoiceService;
use App\Services\LeaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class LeaseController extends Controller
{
    public function __construct(
        protected LeaseService   $leaseService,
        protected InvoiceService $invoiceService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['status', 'tenant_id', 'unit_id']);
        return LeaseResource::collection($this->leaseService->getAllLeases($filters, $request->integer('per_page', 15)));
    }

    public function store(StoreLeaseRequest $request): JsonResponse
    {
        $lease = DB::transaction(function () use ($request) {
            $lease = $this->leaseService->createLease($request->validated());

            // Auto-generate deposit invoice if deposit > 0
            if ($lease->deposit_amount > 0) {
                $this->invoiceService->generateDepositInvoice($lease);
            }

            return $lease;
        });

        return response()->json([
            'success' => true,
            'message' => 'Lease created successfully.',
            'data'    => new LeaseResource($lease->load(['unit.property', 'tenant', 'invoices'])),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $lease = $this->leaseService->getLease($id);
        if (!$lease) {
            return response()->json(['success' => false, 'message' => 'Lease not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => new LeaseResource($lease->load(['unit.property', 'tenant', 'invoices', 'leaseTenants.tenant', 'documents'])),
        ]);
    }

    public function update(UpdateLeaseRequest $request, int $id): JsonResponse
    {
        $lease = $this->leaseService->updateLease($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Lease updated successfully.',
            'data'    => new LeaseResource($lease),
        ]);
    }

    /**
     * POST /api/v1/leases/{id}/renew
     */
    public function renew(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'start_date'  => ['required', 'date'],
            'end_date'    => ['required', 'date', 'after:start_date'],
            'rent_amount' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $lease = $this->leaseService->getLease($id);
        if (!$lease) {
            return response()->json(['success' => false, 'message' => 'Lease not found.'], 404);
        }

        $newLease = DB::transaction(function () use ($lease, $validated) {
            // Expire the old lease
            $this->leaseService->updateLease($lease->id, ['status' => 'expired']);

            // Create the new lease
            $newData = $lease->only(['unit_id', 'tenant_id', 'deposit_amount', 'payment_frequency', 'payment_day_of_month', 'late_fee_amount', 'grace_period_days']);
            $newData['start_date'] = $validated['start_date'];
            $newData['end_date'] = $validated['end_date'];
            $newData['rent_amount'] = $validated['rent_amount'] ?? $lease->rent_amount;
            $newData['status'] = 'active';

            return $this->leaseService->createLease($newData);
        });

        return response()->json([
            'success' => true,
            'message' => 'Lease renewed successfully.',
            'data'    => new LeaseResource($newLease->load(['unit.property', 'tenant'])),
        ], 201);
    }

    /**
     * POST /api/v1/leases/{id}/terminate
     */
    public function terminate(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reason' => ['sometimes', 'string'],
        ]);

        $lease = $this->leaseService->updateLease($id, [
            'status' => 'terminated',
            'notes'  => $request->reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lease terminated.',
            'data'    => new LeaseResource($lease),
        ]);
    }
}
