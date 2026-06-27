<?php

namespace App\Repositories\Eloquent;

use App\Models\Unit;
use App\Repositories\Interfaces\UnitRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class UnitRepository implements UnitRepositoryInterface
{
    public function __construct(
        protected Unit $model
    ) {}

    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with('property');

        if (!empty($filters['property_id'])) {
            $query->where('property_id', $filters['property_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['min_rent'])) {
            $query->where('rent_amount', '>=', $filters['min_rent']);
        }

        if (!empty($filters['max_rent'])) {
            $query->where('rent_amount', '<=', $filters['max_rent']);
        }

        if (!empty($filters['bedrooms'])) {
            $query->where('bedrooms', $filters['bedrooms']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function find(int $id): ?Model
    {
        return $this->model->with(['property', 'leases'])->find($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Model
    {
        $unit = $this->model->findOrFail($id);
        $unit->update($data);

        return $unit->fresh(['property']);
    }

    public function delete(int $id): bool
    {
        $unit = $this->model->findOrFail($id);

        return $unit->delete();
    }

    public function findByProperty(int $propertyId): Collection
    {
        return $this->model->where('property_id', $propertyId)->get();
    }

    public function findAvailableByProperty(int $propertyId): Collection
    {
        return $this->model->where('property_id', $propertyId)
            ->available()
            ->get();
    }

    public function findByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }
}
