<?php

namespace App\Services;

use App\Repositories\Interfaces\MaintenanceRequestRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class MaintenanceRequestService
{
    public function __construct(
        protected MaintenanceRequestRepositoryInterface $maintenanceRepository
    ) {}

    /**
     * Get paginated maintenance requests based on filters and user role.
     */
    public function getAllRequests(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $user = Auth::user();

        // Tenants can only see their own maintenance requests
        if ($user && $user->hasRole('tenant') && !$user->hasAnyRole(['super-admin', 'admin', 'owner'])) {
            $filters['tenant_id'] = $user->id;
        }

        return $this->maintenanceRepository->all($filters, $perPage);
    }

    /**
     * Find a maintenance request by ID.
     */
    public function getRequest(int $id): ?Model
    {
        return $this->maintenanceRepository->find($id);
    }

    /**
     * Create a new maintenance request.
     */
    public function createRequest(array $data): Model
    {
        // Auto-assign the authenticated tenant if not provided
        if (empty($data['tenant_id'])) {
            $data['tenant_id'] = Auth::id();
        }

        return $this->maintenanceRepository->create($data);
    }

    /**
     * Update a maintenance request.
     *
     * Handles resolution: if status changes to 'resolved', sets resolved_at.
     */
    public function updateRequest(int $id, array $data): Model
    {
        // Auto-set resolved_at when status changes to resolved
        if (isset($data['status']) && $data['status'] === 'resolved' && empty($data['resolved_at'])) {
            $data['resolved_at'] = now();
        }

        // Clear resolved_at if reopened
        if (isset($data['status']) && in_array($data['status'], ['open', 'in_progress'])) {
            $data['resolved_at'] = null;
        }

        return $this->maintenanceRepository->update($id, $data);
    }

    /**
     * Delete a maintenance request (soft delete).
     */
    public function deleteRequest(int $id): bool
    {
        return $this->maintenanceRepository->delete($id);
    }

    /**
     * Get unresolved maintenance requests.
     */
    public function getUnresolvedRequests(): Collection
    {
        return $this->maintenanceRepository->findUnresolved();
    }
}
