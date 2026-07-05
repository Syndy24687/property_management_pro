<?php

namespace App\Repositories\Eloquent;

use App\Models\MaintenanceRequest;
use App\Repositories\Interfaces\MaintenanceRequestRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class MaintenanceRequestRepository implements MaintenanceRequestRepositoryInterface
{
    public function __construct(
        protected MaintenanceRequest $model
    ) {}

    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['unit.property', 'tenant', 'category', 'assignee']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['unit_id'])) {
            $query->where('unit_id', $filters['unit_id']);
        }

        if (!empty($filters['tenant_id'])) {
            $query->where('tenant_id', $filters['tenant_id']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function find(int $id): ?Model
    {
        return $this->model->with(['unit.property', 'tenant'])->find($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Model
    {
        $request = $this->model->findOrFail($id);
        $request->update($data);

        return $request->fresh(['unit.property', 'tenant']);
    }

    public function delete(int $id): bool
    {
        $request = $this->model->findOrFail($id);

        return $request->delete();
    }

    public function findByUnit(int $unitId): Collection
    {
        return $this->model->where('unit_id', $unitId)->latest()->get();
    }

    public function findByTenant(int $tenantId): Collection
    {
        return $this->model->where('tenant_id', $tenantId)->latest()->get();
    }

    public function findByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }

    public function findUnresolved(): Collection
    {
        return $this->model->unresolved()
            ->with(['unit.property', 'tenant'])
            ->latest()
            ->get();
    }
}
