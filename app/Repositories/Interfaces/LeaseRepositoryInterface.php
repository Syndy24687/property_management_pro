<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface LeaseRepositoryInterface
{
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Model;

    public function create(array $data): Model;

    public function update(int $id, array $data): Model;

    public function delete(int $id): bool;

    /**
     * Get leases for a specific tenant.
     */
    public function findByTenant(int $tenantId): Collection;

    /**
     * Get leases for a specific unit.
     */
    public function findByUnit(int $unitId): Collection;

    /**
     * Get active lease for a specific unit.
     */
    public function findActiveByUnit(int $unitId): ?Model;

    /**
     * Get leases by status.
     */
    public function findByStatus(string $status): Collection;

    /**
     * Get leases expiring within N days.
     */
    public function findExpiringSoon(int $days = 30): Collection;
}
