<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface UnitRepositoryInterface
{
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Model;

    public function create(array $data): Model;

    public function update(int $id, array $data): Model;

    public function delete(int $id): bool;

    /**
     * Get units belonging to a specific property.
     */
    public function findByProperty(int $propertyId): Collection;

    /**
     * Get available units for a specific property.
     */
    public function findAvailableByProperty(int $propertyId): Collection;

    /**
     * Get units by status.
     */
    public function findByStatus(string $status): Collection;
}
