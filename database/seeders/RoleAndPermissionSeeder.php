<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ─── Define Permissions ────────────────────────────────────
        $permissions = [
            // Company permissions
            'companies.view',
            'companies.create',
            'companies.update',
            'companies.delete',

            // Property permissions
            'properties.view',
            'properties.create',
            'properties.update',
            'properties.delete',

            // Unit permissions
            'units.view',
            'units.create',
            'units.update',
            'units.delete',

            // Lease permissions
            'leases.view',
            'leases.create',
            'leases.update',
            'leases.delete',

            // Invoice permissions
            'invoices.view',
            'invoices.create',
            'invoices.update',
            'invoices.delete',

            // Payment permissions
            'payments.view',
            'payments.create',

            // Utility permissions
            'utility.view',
            'utility.create',
            'utility.update',

            // Maintenance permissions
            'maintenance.view',
            'maintenance.create',
            'maintenance.update',
            'maintenance.delete',

            // Maintenance comments
            'maintenance-comments.view',
            'maintenance-comments.create',
            'maintenance-comments.view-internal',

            // Document permissions
            'documents.view',
            'documents.create',
            'documents.delete',

            // User management
            'users.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
        }

        // ─── Create Roles & Assign Permissions ────────────────────

        // Super Admin — bypasses all checks via Gate::before
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'api']);

        // Admin — full operational access
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $admin->syncPermissions($permissions);

        // Manager — property management operations
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'api']);
        $manager->syncPermissions([
            'properties.view',
            'units.view', 'units.create', 'units.update',
            'leases.view', 'leases.create', 'leases.update',
            'invoices.view', 'invoices.create', 'invoices.update',
            'payments.view', 'payments.create',
            'utility.view', 'utility.create', 'utility.update',
            'maintenance.view', 'maintenance.create', 'maintenance.update',
            'maintenance-comments.view', 'maintenance-comments.create', 'maintenance-comments.view-internal',
            'documents.view', 'documents.create',
        ]);

        // Owner — manages own properties, units, leases, payments
        $owner = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'api']);
        $owner->syncPermissions([
            'properties.view', 'properties.create', 'properties.update', 'properties.delete',
            'units.view', 'units.create', 'units.update', 'units.delete',
            'leases.view', 'leases.create', 'leases.update',
            'invoices.view', 'invoices.create', 'invoices.update',
            'payments.view', 'payments.create',
            'utility.view', 'utility.create', 'utility.update',
            'maintenance.view', 'maintenance.create', 'maintenance.update',
            'maintenance-comments.view', 'maintenance-comments.create', 'maintenance-comments.view-internal',
            'documents.view', 'documents.create', 'documents.delete',
        ]);

        // Tenant — limited access
        $tenant = Role::firstOrCreate(['name' => 'tenant', 'guard_name' => 'api']);
        $tenant->syncPermissions([
            'leases.view',
            'invoices.view',
            'payments.view', 'payments.create',
            'maintenance.view', 'maintenance.create',
            'maintenance-comments.view', 'maintenance-comments.create',
            'documents.view', 'documents.create',
        ]);

        // Occupant — non-primary resident, view-only + maintenance
        $occupant = Role::firstOrCreate(['name' => 'occupant', 'guard_name' => 'api']);
        $occupant->syncPermissions([
            'leases.view',
            'invoices.view',
            'maintenance.view', 'maintenance.create',
            'maintenance-comments.view', 'maintenance-comments.create',
            'documents.view',
        ]);
    }
}
