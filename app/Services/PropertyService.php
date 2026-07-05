<?php

namespace App\Services;

use App\Repositories\Interfaces\PropertyRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class PropertyService
{
    public function __construct(
        protected PropertyRepositoryInterface $propertyRepository
    ) {}

    /**
     * Get paginated properties based on filters and user role.
     */
    public function getAllProperties(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $user = Auth::guard('api')->user();

        // Owners can only see their own properties
        if ($user && $user->hasRole('owner') && !$user->hasAnyRole(['super-admin', 'admin', 'manager'])) {
            $filters['owner_id'] = $user->id;
        }

        return $this->propertyRepository->all($filters, $perPage);
    }

    /**
     * Find a property by ID.
     */
    public function getProperty(int $id): ?Model
    {
        return $this->propertyRepository->find($id);
    }

    /**
     * Create a new property.
     */
    public function createProperty(array $data): Model
    {
        // Auto-assign the authenticated owner if not provided
        if (empty($data['owner_id'])) {
            $data['owner_id'] = Auth::guard('api')->id();
        }

        return $this->propertyRepository->create($data);
    }

    /**
     * Update an existing property.
     */
    public function updateProperty(int $id, array $data): Model
    {
        return $this->propertyRepository->update($id, $data);
    }

    /**
     * Delete a property (soft delete).
     */
    public function deleteProperty(int $id): bool
    {
        return $this->propertyRepository->delete($id);
    }

    /**
     * Get properties by status.
     */
    public function getPropertiesByStatus(string $status): Collection
    {
        return $this->propertyRepository->findByStatus($status);
    }

    /**
     * Get properties by type.
     */
    public function getPropertiesByType(string $type): Collection
    {
        return $this->propertyRepository->findByType($type);
    }
}
