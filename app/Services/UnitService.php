<?php

namespace App\Services;

use App\Repositories\Interfaces\UnitRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class UnitService
{
    public function __construct(
        protected UnitRepositoryInterface $unitRepository
    ) {}

    /**
     * Get paginated units with optional filters.
     */
    public function getAllUnits(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->unitRepository->all($filters, $perPage);
    }

    /**
     * Find a unit by ID.
     */
    public function getUnit(int $id): ?Model
    {
        return $this->unitRepository->find($id);
    }

    /**
     * Create a new unit.
     */
    public function createUnit(array $data): Model
    {
        return $this->unitRepository->create($data);
    }

    /**
     * Update an existing unit.
     */
    public function updateUnit(int $id, array $data): Model
    {
        return $this->unitRepository->update($id, $data);
    }

    /**
     * Delete a unit (soft delete).
     */
    public function deleteUnit(int $id): bool
    {
        return $this->unitRepository->delete($id);
    }

    /**
     * Get all units for a specific property.
     */
    public function getUnitsByProperty(int $propertyId): Collection
    {
        return $this->unitRepository->findByProperty($propertyId);
    }

    /**
     * Get available units for a specific property.
     */
    public function getAvailableUnits(int $propertyId): Collection
    {
        return $this->unitRepository->findAvailableByProperty($propertyId);
    }
}
