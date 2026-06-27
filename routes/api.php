<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\LeaseController;
use App\Http\Controllers\Api\V1\MaintenanceRequestController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PropertyController;
use App\Http\Controllers\Api\V1\TenantController;
use App\Http\Controllers\Api\V1\UnitController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Property Management Pro — REST API v1
| All routes are prefixed with /api/v1
|
*/

Route::prefix('v1')->group(function () {

    // ─── Public / Auth Routes ──────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login',    [AuthController::class, 'login']);
    });

    // ─── Protected Routes (Sanctum) ───────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me',     [AuthController::class, 'me']);

        // Properties
        Route::apiResource('properties', PropertyController::class);
        Route::get('properties/{id}/units', [PropertyController::class, 'units'])
            ->name('properties.units');

        // Units
        Route::apiResource('units', UnitController::class);

        // Leases (no destroy — leases terminated via status update)
        Route::apiResource('leases', LeaseController::class)->except(['destroy']);

        // Payments (create & read only — financial records are immutable)
        Route::apiResource('payments', PaymentController::class)->only(['index', 'store', 'show']);

        // Maintenance Requests
        Route::apiResource('maintenance-requests', MaintenanceRequestController::class)
            ->except(['destroy']);

        // Tenants (read-only)
        Route::apiResource('tenants', TenantController::class)->only(['index', 'show']);
    });
});
