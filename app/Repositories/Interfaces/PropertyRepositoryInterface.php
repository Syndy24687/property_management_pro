<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface PropertyRepositoryInterface
{
    /**
     * Get all properties with optional filters.
     */
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a property by ID.
     */
    public function find(int $id): ?Model;

    /**
     * Create a new property.
     */
    public function create(array $data): Model;

    /**
     * Update an existing property.
     */
    public function update(int $id, array $data): Model;

    /**
     * Delete a property (soft delete).
     */
    public function delete(int $id): bool;

    /**
     * Get properties owned by a specific user.
     */
    public function findByOwner(int $ownerId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get properties by status.
     */
    public function findByStatus(string $status): Collection;

    /**
     * Get properties by type.
     */
    public function findByType(string $type): Collection;
}
