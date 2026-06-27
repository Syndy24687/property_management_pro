<?php

namespace App\Repositories\Eloquent;

use App\Models\Lease;
use App\Repositories\Interfaces\LeaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class LeaseRepository implements LeaseRepositoryInterface
{
    public function __construct(
        protected Lease $model
    ) {}

    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['unit.property', 'tenant']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['tenant_id'])) {
            $query->where('tenant_id', $filters['tenant_id']);
        }

        if (!empty($filters['unit_id'])) {
            $query->where('unit_id', $filters['unit_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function find(int $id): ?Model
    {
        return $this->model->with(['unit.property', 'tenant', 'payments'])->find($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Model
    {
        $lease = $this->model->findOrFail($id);
        $lease->update($data);

        return $lease->fresh(['unit.property', 'tenant']);
    }

    public function delete(int $id): bool
    {
        $lease = $this->model->findOrFail($id);

        return $lease->delete();
    }

    public function findByTenant(int $tenantId): Collection
    {
        return $this->model->with(['unit.property'])
            ->forTenant($tenantId)
            ->get();
    }

    public function findByUnit(int $unitId): Collection
    {
        return $this->model->where('unit_id', $unitId)->get();
    }

    public function findActiveByUnit(int $unitId): ?Model
    {
        return $this->model->where('unit_id', $unitId)
            ->active()
            ->first();
    }

    public function findByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }

    public function findExpiringSoon(int $days = 30): Collection
    {
        return $this->model->with(['unit.property', 'tenant'])
            ->expiringSoon($days)
            ->get();
    }
}
