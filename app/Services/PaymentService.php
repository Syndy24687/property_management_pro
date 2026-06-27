<?php

namespace App\Services;

use App\Repositories\Interfaces\PaymentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class PaymentService
{
    public function __construct(
        protected PaymentRepositoryInterface $paymentRepository
    ) {}

    /**
     * Get paginated payments with optional filters.
     */
    public function getAllPayments(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->paymentRepository->all($filters, $perPage);
    }

    /**
     * Find a payment by ID.
     */
    public function getPayment(int $id): ?Model
    {
        return $this->paymentRepository->find($id);
    }

    /**
     * Record a new payment.
     *
     * If status is 'completed', automatically sets payment_date to today.
     */
    public function createPayment(array $data): Model
    {
        // Auto-set payment_date for completed payments
        if (($data['status'] ?? 'pending') === 'completed' && empty($data['payment_date'])) {
            $data['payment_date'] = now()->toDateString();
        }

        return $this->paymentRepository->create($data);
    }

    /**
     * Update a payment.
     *
     * Handles status transitions (e.g., pending → completed sets payment_date).
     */
    public function updatePayment(int $id, array $data): Model
    {
        // If marking as completed and no payment_date provided
        if (isset($data['status']) && $data['status'] === 'completed' && empty($data['payment_date'])) {
            $data['payment_date'] = now()->toDateString();
        }

        return $this->paymentRepository->update($id, $data);
    }

    /**
     * Get payments for a specific lease.
     */
    public function getPaymentsByLease(int $leaseId): Collection
    {
        return $this->paymentRepository->findByLease($leaseId);
    }

    /**
     * Get overdue payments.
     */
    public function getOverduePayments(): Collection
    {
        return $this->paymentRepository->findOverdue();
    }
}
