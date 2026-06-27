<?php

namespace App\Services;

use App\Repositories\Interfaces\TenantRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class TenantService
{
    public function __construct(
        protected TenantRepositoryInterface $tenantRepository
    ) {}

    /**
     * Get paginated tenants with optional filters.
     */
    public function getAllTenants(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->tenantRepository->all($filters, $perPage);
    }

    /**
     * Find a tenant by ID.
     */
    public function getTenant(int $id): ?Model
    {
        return $this->tenantRepository->find($id);
    }

    /**
     * Get tenants by property.
     */
    public function getTenantsByProperty(int $propertyId): Collection
    {
        return $this->tenantRepository->findByProperty($propertyId);
    }

    /**
     * Get active tenants (those with active leases).
     */
    public function getActiveTenants(): Collection
    {
        return $this->tenantRepository->findActive();
    }
}
