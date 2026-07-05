<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * GET /api/v1/invoices
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['status', 'lease_id', 'tenant_id', 'from_date', 'to_date']);
        $perPage = $request->integer('per_page', 15);

        // Scope to tenant's own invoices if they're a tenant
        $user = auth('api')->user();
        if ($user->hasRole('tenant') && !$user->hasAnyRole(['super-admin', 'admin', 'owner', 'manager'])) {
            $filters['tenant_id'] = $user->id;
        }

        return InvoiceResource::collection($this->invoiceService->getAll($filters, $perPage));
    }

    /**
     * POST /api/v1/invoices (manual creation)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lease_id'          => ['required', 'exists:leases,id'],
            'due_date'          => ['required', 'date'],
            'issue_date'        => ['sometimes', 'date'],
            'notes'             => ['nullable', 'string'],
            'status'            => ['sometimes', 'in:draft,sent'],
            'items'             => ['required', 'array', 'min:1'],
            'items.*.type'      => ['required', 'in:rent,deposit,late_fee,utility,maintenance,other'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity'  => ['sometimes', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $invoice = $this->invoiceService->createManual($validated);

        return response()->json([
            'success' => true,
            'message' => 'Invoice created successfully.',
            'data'    => new InvoiceResource($invoice),
        ], 201);
    }

    /**
     * GET /api/v1/invoices/{id}
     */
    public function show(int $id): JsonResponse
    {
        $invoice = $this->invoiceService->find($id);

        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Invoice not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => new InvoiceResource($invoice),
        ]);
    }

    /**
     * PUT /api/v1/invoices/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'notes'  => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'in:draft,sent,cancelled'],
        ]);

        $invoice = $this->invoiceService->find($id);
        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Invoice not found.'], 404);
        }

        $invoice->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Invoice updated successfully.',
            'data'    => new InvoiceResource($invoice->fresh()),
        ]);
    }

    /**
     * POST /api/v1/invoices/{id}/send
     */
    public function send(int $id): JsonResponse
    {
        $invoice = $this->invoiceService->find($id);
        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Invoice not found.'], 404);
        }

        $this->invoiceService->markAsSent($invoice);

        return response()->json([
            'success' => true,
            'message' => 'Invoice marked as sent.',
        ]);
    }

    /**
     * POST /api/v1/invoices/{id}/void
     */
    public function void(int $id): JsonResponse
    {
        $invoice = $this->invoiceService->find($id);
        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Invoice not found.'], 404);
        }

        $this->invoiceService->voidInvoice($invoice);

        return response()->json([
            'success' => true,
            'message' => 'Invoice voided successfully.',
        ]);
    }
}
