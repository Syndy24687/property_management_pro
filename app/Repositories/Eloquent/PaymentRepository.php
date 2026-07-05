<?php

namespace App\Repositories\Eloquent;

use App\Models\Payment;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function __construct(
        protected Payment $model
    ) {}

    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['lease.tenant', 'lease.unit.property', 'invoice']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['lease_id'])) {
            $query->where('lease_id', $filters['lease_id']);
        }

        if (!empty($filters['invoice_id'])) {
            $query->where('invoice_id', $filters['invoice_id']);
        }

        if (!empty($filters['method'])) {
            $query->where('method', $filters['method']);
        }

        if (!empty($filters['from_date'])) {
            $query->where('due_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->where('due_date', '<=', $filters['to_date']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function find(int $id): ?Model
    {
        return $this->model->with(['lease.tenant', 'lease.unit.property'])->find($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Model
    {
        $payment = $this->model->findOrFail($id);
        $payment->update($data);

        return $payment->fresh(['lease.tenant']);
    }

    public function findByLease(int $leaseId): Collection
    {
        return $this->model->where('lease_id', $leaseId)
            ->orderBy('due_date', 'desc')
            ->get();
    }

    public function findByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }

    public function findOverdue(): Collection
    {
        return $this->model->overdue()
            ->with(['lease.tenant', 'lease.unit.property'])
            ->get();
    }
}
