<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Display a paginated list of payments.
     *
     * GET /api/v1/payments
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['status', 'lease_id', 'method', 'from_date', 'to_date']);
        $perPage = $request->integer('per_page', 15);

        $payments = $this->paymentService->getAllPayments($filters, $perPage);

        return PaymentResource::collection($payments);
    }

    /**
     * Store a newly created payment.
     *
     * POST /api/v1/payments
     */
    public function store(StorePaymentRequest $request): JsonResponse
    {
        $payment = $this->paymentService->createPayment($request->validated());

        return (new PaymentResource($payment))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified payment.
     *
     * GET /api/v1/payments/{id}
     */
    public function show(int $id): JsonResponse
    {
        $payment = $this->paymentService->getPayment($id);

        if (!$payment) {
            return response()->json(['message' => 'Payment not found.'], 404);
        }

        return (new PaymentResource($payment))->response();
    }
}
