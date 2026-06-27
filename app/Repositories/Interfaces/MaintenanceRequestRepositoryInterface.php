<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface MaintenanceRequestRepositoryInterface
{
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Model;

    public function create(array $data): Model;

    public function update(int $id, array $data): Model;

    public function delete(int $id): bool;

    /**
     * Get maintenance requests for a specific unit.
     */
    public function findByUnit(int $unitId): Collection;

    /**
     * Get maintenance requests by a specific tenant.
     */
    public function findByTenant(int $tenantId): Collection;

    /**
     * Get maintenance requests by status.
     */
    public function findByStatus(string $status): Collection;

    /**
     * Get unresolved maintenance requests (open or in_progress).
     */
    public function findUnresolved(): Collection;
}
