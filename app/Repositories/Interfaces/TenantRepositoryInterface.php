<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface TenantRepositoryInterface
{
    /**
     * Get all tenants (users with the 'tenant' role).
     */
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a tenant by ID.
     */
    public function find(int $id): ?Model;

    /**
     * Get tenants for a specific property (via leases).
     */
    public function findByProperty(int $propertyId): Collection;

    /**
     * Get active tenants (those with active leases).
     */
    public function findActive(): Collection;
}
