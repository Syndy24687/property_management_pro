<?php

namespace App\Services;

use App\Repositories\Interfaces\LeaseRepositoryInterface;
use App\Repositories\Interfaces\UnitRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaseService
{
    public function __construct(
        protected LeaseRepositoryInterface $leaseRepository,
        protected UnitRepositoryInterface  $unitRepository
    ) {}

    /**
     * Get paginated leases based on filters and user role.
     */
    public function getAllLeases(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $user = Auth::user();

        // Tenants can only see their own leases
        if ($user && $user->hasRole('tenant') && !$user->hasAnyRole(['super-admin', 'admin', 'owner'])) {
            $filters['tenant_id'] = $user->id;
        }

        return $this->leaseRepository->all($filters, $perPage);
    }

    /**
     * Find a lease by ID.
     */
    public function getLease(int $id): ?Model
    {
        return $this->leaseRepository->find($id);
    }

    /**
     * Create a new lease with business logic.
     *
     * - Validates unit is available
     * - Activates the lease
     * - Updates unit status to 'occupied'
     */
    public function createLease(array $data): Model
    {
        // Check if the unit already has an active lease
        $activeLease = $this->leaseRepository->findActiveByUnit($data['unit_id']);

        if ($activeLease) {
            throw ValidationException::withMessages([
                'unit_id' => ['This unit already has an active lease.'],
            ]);
        }

        return DB::transaction(function () use ($data) {
            // Create the lease
            $lease = $this->leaseRepository->create($data);

            // If lease is active, mark the unit as occupied
            if (($data['status'] ?? 'pending') === 'active') {
                $this->unitRepository->update($data['unit_id'], ['status' => 'occupied']);
            }

            return $lease->load(['unit.property', 'tenant']);
        });
    }

    /**
     * Update a lease with business logic.
     *
     * - Handles status transitions (active → expired, etc.)
     * - Updates unit status accordingly
     */
    public function updateLease(int $id, array $data): Model
    {
        return DB::transaction(function () use ($id, $data) {
            $lease = $this->leaseRepository->find($id);

            // Handle status transitions
            if (isset($data['status'])) {
                $oldStatus = $lease->status;
                $newStatus = $data['status'];

                // Activating a lease → mark unit as occupied
                if ($oldStatus !== 'active' && $newStatus === 'active') {
                    $this->unitRepository->update($lease->unit_id, ['status' => 'occupied']);
                }

                // Terminating or expiring a lease → mark unit as available
                if ($oldStatus === 'active' && in_array($newStatus, ['expired', 'terminated'])) {
                    $this->unitRepository->update($lease->unit_id, ['status' => 'available']);
                }
            }

            return $this->leaseRepository->update($id, $data);
        });
    }

    /**
     * Get leases expiring soon.
     */
    public function getExpiringSoon(int $days = 30): Collection
    {
        return $this->leaseRepository->findExpiringSoon($days);
    }
}
