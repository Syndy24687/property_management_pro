<?php

namespace App\Repositories\Eloquent;

use App\Models\Property;
use App\Repositories\Interfaces\PropertyRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class PropertyRepository implements PropertyRepositoryInterface
{
    public function __construct(
        protected Property $model
    ) {}

    /**
     * Get all properties with optional filters.
     */
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with('owner');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['city'])) {
            $query->where('city', 'LIKE', '%' . $filters['city'] . '%');
        }

        if (!empty($filters['state'])) {
            $query->where('state', $filters['state']);
        }

        if (!empty($filters['owner_id'])) {
            $query->where('owner_id', $filters['owner_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%")
                  ->orWhere('city', 'LIKE', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Find a property by ID with relationships.
     */
    public function find(int $id): ?Model
    {
        return $this->model->with(['owner', 'units'])->find($id);
    }

    /**
     * Create a new property.
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing property.
     */
    public function update(int $id, array $data): Model
    {
        $property = $this->model->findOrFail($id);
        $property->update($data);

        return $property->fresh(['owner', 'units']);
    }

    /**
     * Delete a property (soft delete).
     */
    public function delete(int $id): bool
    {
        $property = $this->model->findOrFail($id);

        return $property->delete();
    }

    /**
     * Get properties owned by a specific user.
     */
    public function findByOwner(int $ownerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with('owner')
            ->ownedBy($ownerId)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get properties by status.
     */
    public function findByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }

    /**
     * Get properties by type.
     */
    public function findByType(string $type): Collection
    {
        return $this->model->ofType($type)->get();
    }
}
