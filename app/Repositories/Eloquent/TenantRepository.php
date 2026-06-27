<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Interfaces\TenantRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class TenantRepository implements TenantRepositoryInterface
{
    public function __construct(
        protected User $model
    ) {}

    /**
     * Get all users with the 'tenant' role.
     */
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->role('tenant');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Find a tenant by ID (must have the 'tenant' role).
     */
    public function find(int $id): ?Model
    {
        return $this->model->role('tenant')
            ->with(['leases.unit.property'])
            ->find($id);
    }

    /**
     * Get tenants for a specific property via lease relationships.
     */
    public function findByProperty(int $propertyId): Collection
    {
        return $this->model->role('tenant')
            ->whereHas('leases.unit', function ($query) use ($propertyId) {
                $query->where('property_id', $propertyId);
            })
            ->get();
    }

    /**
     * Get tenants with active leases.
     */
    public function findActive(): Collection
    {
        return $this->model->role('tenant')
            ->whereHas('leases', function ($query) {
                $query->where('status', 'active');
            })
            ->get();
    }
}
