<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface PaymentRepositoryInterface
{
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Model;

    public function create(array $data): Model;

    public function update(int $id, array $data): Model;

    /**
     * Get payments for a specific lease.
     */
    public function findByLease(int $leaseId): Collection;

    /**
     * Get payments by status.
     */
    public function findByStatus(string $status): Collection;

    /**
     * Get overdue payments.
     */
    public function findOverdue(): Collection;
}
