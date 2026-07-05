<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected InvoiceService $invoiceService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['status', 'lease_id', 'invoice_id', 'method', 'from_date', 'to_date']);
        return PaymentResource::collection($this->paymentService->getAllPayments($filters, $request->integer('per_page', 15)));
    }

    /**
     * POST /api/v1/payments
     * Record a payment — auto-updates invoice balance if invoice_id is provided.
     */
    public function store(StorePaymentRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['received_by'] = auth('api')->id();

        $payment = DB::transaction(function () use ($data) {
            $payment = $this->paymentService->createPayment($data);

            // Auto-update invoice balance
            if (!empty($data['invoice_id'])) {
                $invoice = Invoice::findOrFail($data['invoice_id']);
                $this->invoiceService->recordPayment($invoice, $data['amount']);
            }

            return $payment;
        });

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully.',
            'data'    => new PaymentResource($payment->load(['lease.tenant', 'invoice'])),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $payment = $this->paymentService->getPayment($id);

        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => new PaymentResource($payment->load(['lease.tenant', 'invoice'])),
        ]);
    }
}
