<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Interfaces
use App\Repositories\Interfaces\PropertyRepositoryInterface;
use App\Repositories\Interfaces\UnitRepositoryInterface;
use App\Repositories\Interfaces\LeaseRepositoryInterface;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use App\Repositories\Interfaces\MaintenanceRequestRepositoryInterface;
use App\Repositories\Interfaces\TenantRepositoryInterface;

// Eloquent Implementations
use App\Repositories\Eloquent\PropertyRepository;
use App\Repositories\Eloquent\UnitRepository;
use App\Repositories\Eloquent\LeaseRepository;
use App\Repositories\Eloquent\PaymentRepository;
use App\Repositories\Eloquent\MaintenanceRequestRepository;
use App\Repositories\Eloquent\TenantRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All repository interface-to-implementation bindings.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        PropertyRepositoryInterface::class           => PropertyRepository::class,
        UnitRepositoryInterface::class               => UnitRepository::class,
        LeaseRepositoryInterface::class              => LeaseRepository::class,
        PaymentRepositoryInterface::class            => PaymentRepository::class,
        MaintenanceRequestRepositoryInterface::class => MaintenanceRequestRepository::class,
        TenantRepositoryInterface::class             => TenantRepository::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
