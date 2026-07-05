<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\LeaseController;
use App\Http\Controllers\Api\V1\MaintenanceRequestController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PropertyController;
use App\Http\Controllers\Api\V1\SystemSettingController;
use App\Http\Controllers\Api\V1\TenantController;
use App\Http\Controllers\Api\V1\UnitController;
use App\Http\Controllers\Api\V1\UtilityController;
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

    // ─── Protected Routes (JWT) ────────────────────────────────
    Route::middleware('jwt.auth')->group(function () {

        // Auth
        Route::post('/auth/logout',          [AuthController::class, 'logout']);
        Route::post('/auth/refresh',         [AuthController::class, 'refresh']);
        Route::get('/auth/me',               [AuthController::class, 'me']);
        Route::put('/auth/profile',          [AuthController::class, 'updateProfile']);
        Route::put('/auth/change-password',  [AuthController::class, 'changePassword']);

        // ─── System Settings (Admin+) ──────────────────────────
        Route::middleware('check.role:admin')->group(function () {
            Route::get('/settings',          [SystemSettingController::class, 'index']);
            Route::get('/settings/{group}',  [SystemSettingController::class, 'show']);
        });
        Route::middleware('check.role:admin')->group(function () {
            Route::put('/settings',          [SystemSettingController::class, 'update']);
        });

        // ─── Properties ────────────────────────────────────────
        Route::apiResource('properties', PropertyController::class);
        Route::get('properties/{id}/units', [PropertyController::class, 'units']);

        // Property Images (Owner+)
        Route::post('properties/{id}/images',                [PropertyController::class, 'uploadImages']);
        Route::delete('properties/{propertyId}/images/{imageId}', [PropertyController::class, 'deleteImage']);

        // ─── Units ─────────────────────────────────────────────
        Route::apiResource('units', UnitController::class);
        Route::post('units/{id}/images',              [UnitController::class, 'uploadImages']);
        Route::delete('units/{unitId}/images/{imageId}', [UnitController::class, 'deleteImage']);

        // ─── Leases ────────────────────────────────────────────
        Route::apiResource('leases', LeaseController::class)->except(['destroy']);
        Route::post('leases/{id}/renew',     [LeaseController::class, 'renew']);
        Route::post('leases/{id}/terminate', [LeaseController::class, 'terminate']);

        // ─── Invoices ──────────────────────────────────────────
        Route::apiResource('invoices', InvoiceController::class)->except(['destroy']);
        Route::post('invoices/{id}/send',    [InvoiceController::class, 'send']);
        Route::post('invoices/{id}/void',    [InvoiceController::class, 'void']);

        // ─── Payments ──────────────────────────────────────────
        Route::apiResource('payments', PaymentController::class)->only(['index', 'store', 'show']);

        // ─── Utilities ─────────────────────────────────────────
        // Utility Types (tariffs)
        Route::get('utility-types',       [UtilityController::class, 'indexTypes']);
        Route::post('utility-types',      [UtilityController::class, 'storeType']);
        Route::put('utility-types/{id}',  [UtilityController::class, 'updateType']);

        // Meters
        Route::get('units/{unitId}/meters',  [UtilityController::class, 'indexMeters']);
        Route::post('units/{unitId}/meters', [UtilityController::class, 'storeMeter']);
        Route::put('meters/{id}',            [UtilityController::class, 'updateMeter']);
        Route::delete('meters/{id}',         [UtilityController::class, 'destroyMeter']);

        // Meter Readings
        Route::get('meters/{meterId}/readings',  [UtilityController::class, 'indexReadings']);
        Route::post('meters/{meterId}/readings', [UtilityController::class, 'storeReading']);

        // Utility Charges
        Route::get('utility-charges', [UtilityController::class, 'indexCharges']);

        // ─── Maintenance ───────────────────────────────────────
        Route::apiResource('maintenance-requests', MaintenanceRequestController::class)->except(['destroy']);
        Route::post('maintenance-requests/{id}/assign',       [MaintenanceRequestController::class, 'assign']);
        Route::get('maintenance-requests/{id}/comments',      [MaintenanceRequestController::class, 'indexComments']);
        Route::post('maintenance-requests/{id}/comments',     [MaintenanceRequestController::class, 'storeComment']);
        Route::get('maintenance-requests/{id}/attachments',   [MaintenanceRequestController::class, 'indexAttachments']);
        Route::post('maintenance-requests/{id}/attachments',  [MaintenanceRequestController::class, 'storeAttachment']);

        // ─── Tenants (read-only) ───────────────────────────────
        Route::apiResource('tenants', TenantController::class)->only(['index', 'show']);
    });
});
