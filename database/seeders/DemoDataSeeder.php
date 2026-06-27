<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Document;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Lease;
use App\Models\LeaseTenant;
use App\Models\MaintenanceCategory;
use App\Models\MaintenanceComment;
use App\Models\MaintenanceRequest;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyManager;
use App\Models\Unit;
use App\Models\User;
use App\Models\UtilityCharge;
use App\Models\UtilityMeter;
use App\Models\UtilityType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed demo data for development and testing.
     */
    public function run(): void
    {
        // ─── Company ───────────────────────────────────────────────

        $company = Company::create([
            'name'     => 'Apex Property Group',
            'email'    => 'info@apexpropertygroup.test',
            'phone'    => '555-100-0001',
            'address'  => '100 Corporate Blvd',
            'city'     => 'Los Angeles',
            'state'    => 'CA',
            'zip_code' => '90001',
            'status'   => 'active',
        ]);

        // ─── Users ─────────────────────────────────────────────────

        $superAdmin = User::create([
            'name' => 'Super Admin', 'email' => 'superadmin@propertymanagement.test',
            'password' => Hash::make('password'), 'phone' => '555-000-0001',
            'company_id' => $company->id, 'status' => 'active',
        ]);
        $superAdmin->assignRole('super-admin');

        $admin = User::create([
            'name' => 'Admin User', 'email' => 'admin@propertymanagement.test',
            'password' => Hash::make('password'), 'phone' => '555-000-0002',
            'company_id' => $company->id, 'status' => 'active',
        ]);
        $admin->assignRole('admin');

        $manager1 = User::create([
            'name' => 'Sarah Manager', 'email' => 'manager@propertymanagement.test',
            'password' => Hash::make('password'), 'phone' => '555-000-0010',
            'company_id' => $company->id, 'status' => 'active',
        ]);
        $manager1->assignRole('manager');

        $owner1 = User::create([
            'name' => 'John Landlord', 'email' => 'owner@propertymanagement.test',
            'password' => Hash::make('password'), 'phone' => '555-000-0003',
            'company_id' => $company->id, 'status' => 'active',
        ]);
        $owner1->assignRole('owner');

        $owner2 = User::create([
            'name' => 'Jane Properties', 'email' => 'owner2@propertymanagement.test',
            'password' => Hash::make('password'), 'phone' => '555-000-0004',
            'company_id' => $company->id, 'status' => 'active',
        ]);
        $owner2->assignRole('owner');

        $tenant1 = User::create([
            'name' => 'Alice Tenant', 'email' => 'tenant@propertymanagement.test',
            'password' => Hash::make('password'), 'phone' => '555-000-0005',
            'emergency_contact_name' => 'Bob Tenant', 'emergency_contact_phone' => '555-999-0001',
            'date_of_birth' => '1990-03-15', 'status' => 'active',
        ]);
        $tenant1->assignRole('tenant');

        $tenant2 = User::create([
            'name' => 'Bob Renter', 'email' => 'tenant2@propertymanagement.test',
            'password' => Hash::make('password'), 'phone' => '555-000-0006',
            'emergency_contact_name' => 'Carol Renter', 'emergency_contact_phone' => '555-999-0002',
            'date_of_birth' => '1988-07-22', 'status' => 'active',
        ]);
        $tenant2->assignRole('tenant');

        $tenant3 = User::create([
            'name' => 'Carol Resident', 'email' => 'tenant3@propertymanagement.test',
            'password' => Hash::make('password'), 'phone' => '555-000-0007',
            'date_of_birth' => '1995-11-08', 'status' => 'active',
        ]);
        $tenant3->assignRole('tenant');

        // ─── Properties ────────────────────────────────────────────

        $property1 = Property::create([
            'owner_id' => $owner1->id, 'company_id' => $company->id,
            'name' => 'Sunset Apartments', 'address' => '123 Sunset Boulevard',
            'city' => 'Los Angeles', 'state' => 'CA', 'zip_code' => '90028',
            'type' => 'residential', 'description' => 'A luxurious apartment complex with panoramic city views.',
            'latitude' => 34.0983, 'longitude' => -118.3267, 'year_built' => 2018, 'status' => 'active',
        ]);

        $property2 = Property::create([
            'owner_id' => $owner1->id, 'company_id' => $company->id,
            'name' => 'Downtown Office Tower', 'address' => '456 Business Ave',
            'city' => 'San Francisco', 'state' => 'CA', 'zip_code' => '94102',
            'type' => 'commercial', 'description' => 'Modern office spaces in the heart of the financial district.',
            'latitude' => 37.7849, 'longitude' => -122.4094, 'year_built' => 2015, 'status' => 'active',
        ]);

        $property3 = Property::create([
            'owner_id' => $owner2->id, 'company_id' => $company->id,
            'name' => 'Green Valley Homes', 'address' => '789 Meadow Lane',
            'city' => 'Portland', 'state' => 'OR', 'zip_code' => '97201',
            'type' => 'residential', 'description' => 'Eco-friendly residential community surrounded by nature.',
            'latitude' => 45.5152, 'longitude' => -122.6784, 'year_built' => 2020, 'status' => 'active',
        ]);

        // ─── Property Managers ─────────────────────────────────────

        PropertyManager::create([
            'property_id' => $property1->id, 'user_id' => $manager1->id,
            'assigned_at' => '2026-01-01', 'is_primary' => true,
        ]);
        PropertyManager::create([
            'property_id' => $property3->id, 'user_id' => $manager1->id,
            'assigned_at' => '2026-03-01', 'is_primary' => true,
        ]);

        // ─── Units ─────────────────────────────────────────────────

        $unit1 = Unit::create([
            'property_id' => $property1->id, 'unit_number' => '101', 'floor' => 1,
            'bedrooms' => 2, 'bathrooms' => 1, 'area_sqft' => 850.00,
            'rent_amount' => 2200.00, 'deposit_amount' => 4400.00, 'status' => 'occupied',
        ]);
        $unit2 = Unit::create([
            'property_id' => $property1->id, 'unit_number' => '102', 'floor' => 1,
            'bedrooms' => 3, 'bathrooms' => 2, 'area_sqft' => 1200.00,
            'rent_amount' => 3100.00, 'deposit_amount' => 6200.00, 'status' => 'occupied',
        ]);
        $unit3 = Unit::create([
            'property_id' => $property1->id, 'unit_number' => '201', 'floor' => 2,
            'bedrooms' => 1, 'bathrooms' => 1, 'area_sqft' => 650.00,
            'rent_amount' => 1800.00, 'deposit_amount' => 3600.00, 'status' => 'available',
        ]);
        $unit4 = Unit::create([
            'property_id' => $property2->id, 'unit_number' => 'Suite 300', 'floor' => 3,
            'bedrooms' => 0, 'bathrooms' => 1, 'area_sqft' => 2500.00,
            'rent_amount' => 8500.00, 'deposit_amount' => 17000.00, 'status' => 'available',
        ]);
        $unit5 = Unit::create([
            'property_id' => $property3->id, 'unit_number' => 'A', 'floor' => 1,
            'bedrooms' => 3, 'bathrooms' => 2, 'area_sqft' => 1500.00,
            'rent_amount' => 2800.00, 'deposit_amount' => 5600.00, 'status' => 'occupied',
        ]);

        // ─── Leases ────────────────────────────────────────────────

        $lease1 = Lease::create([
            'unit_id' => $unit1->id, 'tenant_id' => $tenant1->id,
            'start_date' => '2026-01-01', 'end_date' => '2026-12-31',
            'rent_amount' => 2200.00, 'deposit_amount' => 4400.00,
            'payment_frequency' => 'monthly', 'payment_day_of_month' => 1,
            'late_fee_amount' => 50.00, 'grace_period_days' => 5,
            'status' => 'active',
        ]);
        $lease2 = Lease::create([
            'unit_id' => $unit2->id, 'tenant_id' => $tenant2->id,
            'start_date' => '2026-03-01', 'end_date' => '2027-02-28',
            'rent_amount' => 3100.00, 'deposit_amount' => 6200.00,
            'payment_frequency' => 'monthly', 'payment_day_of_month' => 1,
            'late_fee_amount' => 75.00, 'grace_period_days' => 5,
            'auto_renew' => true, 'status' => 'active',
        ]);
        $lease3 = Lease::create([
            'unit_id' => $unit5->id, 'tenant_id' => $tenant3->id,
            'start_date' => '2026-06-01', 'end_date' => '2027-05-31',
            'rent_amount' => 2800.00, 'deposit_amount' => 5600.00,
            'payment_frequency' => 'monthly', 'payment_day_of_month' => 1,
            'late_fee_amount' => 60.00, 'grace_period_days' => 3,
            'status' => 'active',
        ]);

        // ─── Lease Tenants (co-tenants) ────────────────────────────

        LeaseTenant::create(['lease_id' => $lease1->id, 'tenant_id' => $tenant1->id, 'is_primary' => true]);
        LeaseTenant::create(['lease_id' => $lease2->id, 'tenant_id' => $tenant2->id, 'is_primary' => true]);
        LeaseTenant::create(['lease_id' => $lease3->id, 'tenant_id' => $tenant3->id, 'is_primary' => true]);

        // ─── Utility Types ─────────────────────────────────────────

        $elec = UtilityType::create(['name' => 'Electricity', 'unit_of_measure' => 'kWh', 'default_rate' => 0.1200]);
        $water = UtilityType::create(['name' => 'Water', 'unit_of_measure' => 'gallons', 'default_rate' => 0.0050]);
        $gas = UtilityType::create(['name' => 'Gas', 'unit_of_measure' => 'therms', 'default_rate' => 1.2000]);
        UtilityType::create(['name' => 'Sewage', 'unit_of_measure' => 'flat', 'default_rate' => 35.0000]);
        UtilityType::create(['name' => 'Trash', 'unit_of_measure' => 'flat', 'default_rate' => 25.0000]);
        UtilityType::create(['name' => 'Internet', 'unit_of_measure' => 'flat', 'default_rate' => 65.0000]);

        // ─── Utility Meters ────────────────────────────────────────

        $meter1 = UtilityMeter::create([
            'unit_id' => $unit1->id, 'utility_type_id' => $elec->id,
            'meter_number' => 'EM-101-001', 'installation_date' => '2025-01-15',
        ]);
        $meter2 = UtilityMeter::create([
            'unit_id' => $unit1->id, 'utility_type_id' => $water->id,
            'meter_number' => 'WM-101-001', 'installation_date' => '2025-01-15',
        ]);
        $meter3 = UtilityMeter::create([
            'unit_id' => $unit2->id, 'utility_type_id' => $elec->id,
            'meter_number' => 'EM-102-001', 'installation_date' => '2025-01-15',
        ]);

        // ─── Meter Readings ────────────────────────────────────────

        $reading1 = MeterReading::create([
            'utility_meter_id' => $meter1->id, 'read_by' => $manager1->id,
            'reading_date' => '2026-06-01',
            'reading_value' => 5420.00, 'previous_value' => 5050.00,
        ]);
        $reading2 = MeterReading::create([
            'utility_meter_id' => $meter2->id, 'read_by' => $manager1->id,
            'reading_date' => '2026-06-01',
            'reading_value' => 12800.00, 'previous_value' => 11500.00,
        ]);

        // ─── Utility Charges ───────────────────────────────────────

        $uCharge1 = UtilityCharge::create([
            'utility_meter_id' => $meter1->id, 'meter_reading_id' => $reading1->id,
            'billing_period_start' => '2026-05-01', 'billing_period_end' => '2026-05-31',
            'usage' => 370.00, 'rate' => 0.1200, 'status' => 'invoiced',
        ]);
        $uCharge2 = UtilityCharge::create([
            'utility_meter_id' => $meter2->id, 'meter_reading_id' => $reading2->id,
            'billing_period_start' => '2026-05-01', 'billing_period_end' => '2026-05-31',
            'usage' => 1300.00, 'rate' => 0.0050, 'status' => 'invoiced',
        ]);

        // ─── Invoices ──────────────────────────────────────────────

        // June 2026 invoice for lease1
        $invoice1 = Invoice::create([
            'lease_id' => $lease1->id, 'invoice_number' => 'INV-2026-0001',
            'issue_date' => '2026-06-01', 'due_date' => '2026-06-05',
            'subtotal' => 2250.90, 'tax_amount' => 0,
            'total_amount' => 2250.90, 'amount_paid' => 2250.90, 'balance_due' => 0,
            'status' => 'paid',
        ]);
        InvoiceItem::create([
            'invoice_id' => $invoice1->id, 'type' => 'rent',
            'description' => 'Monthly Rent — June 2026', 'quantity' => 1, 'unit_price' => 2200.00, 'amount' => 2200.00,
        ]);
        InvoiceItem::create([
            'invoice_id' => $invoice1->id, 'type' => 'utility',
            'description' => 'Electricity — May 2026 (370 kWh)', 'quantity' => 1, 'unit_price' => 44.40, 'amount' => 44.40,
            'utility_charge_id' => $uCharge1->id,
        ]);
        InvoiceItem::create([
            'invoice_id' => $invoice1->id, 'type' => 'utility',
            'description' => 'Water — May 2026 (1,300 gal)', 'quantity' => 1, 'unit_price' => 6.50, 'amount' => 6.50,
            'utility_charge_id' => $uCharge2->id,
        ]);

        // July 2026 invoice for lease1 (unpaid)
        $invoice2 = Invoice::create([
            'lease_id' => $lease1->id, 'invoice_number' => 'INV-2026-0002',
            'issue_date' => '2026-07-01', 'due_date' => '2026-07-05',
            'subtotal' => 2200.00, 'tax_amount' => 0,
            'total_amount' => 2200.00, 'amount_paid' => 0, 'balance_due' => 2200.00,
            'status' => 'sent',
        ]);
        InvoiceItem::create([
            'invoice_id' => $invoice2->id, 'type' => 'rent',
            'description' => 'Monthly Rent — July 2026', 'quantity' => 1, 'unit_price' => 2200.00, 'amount' => 2200.00,
        ]);

        // ─── Payments ──────────────────────────────────────────────

        Payment::create([
            'lease_id' => $lease1->id, 'invoice_id' => $invoice1->id,
            'amount' => 2250.90, 'payment_date' => '2026-06-01', 'due_date' => '2026-06-05',
            'method' => 'bank_transfer', 'status' => 'completed',
            'reference_number' => 'PAY-2026-0601', 'received_by' => $manager1->id,
        ]);

        // Previous months (no invoices — legacy direct payments)
        foreach (['01', '02', '03', '04', '05'] as $month) {
            Payment::create([
                'lease_id' => $lease1->id, 'amount' => 2200.00,
                'payment_date' => "2026-{$month}-01", 'due_date' => "2026-{$month}-01",
                'method' => 'bank_transfer', 'status' => 'completed',
                'reference_number' => "PAY-{$lease1->id}-2026{$month}",
            ]);
        }

        foreach (['03', '04', '05', '06'] as $month) {
            Payment::create([
                'lease_id' => $lease2->id, 'amount' => 3100.00,
                'payment_date' => "2026-{$month}-01", 'due_date' => "2026-{$month}-01",
                'method' => 'online', 'status' => 'completed',
                'reference_number' => "PAY-{$lease2->id}-2026{$month}",
            ]);
        }

        // ─── Maintenance Categories ────────────────────────────────

        $catPlumbing = MaintenanceCategory::create(['name' => 'Plumbing', 'description' => 'Water pipes, faucets, drains, toilets', 'icon' => 'wrench']);
        $catElectrical = MaintenanceCategory::create(['name' => 'Electrical', 'description' => 'Wiring, outlets, switches, breakers', 'icon' => 'zap']);
        $catHvac = MaintenanceCategory::create(['name' => 'HVAC', 'description' => 'Heating, ventilation, and air conditioning', 'icon' => 'thermometer']);
        $catAppliance = MaintenanceCategory::create(['name' => 'Appliance', 'description' => 'Refrigerators, washers, dryers, dishwashers', 'icon' => 'settings']);
        MaintenanceCategory::create(['name' => 'Structural', 'description' => 'Walls, floors, ceilings, windows, doors', 'icon' => 'home']);
        MaintenanceCategory::create(['name' => 'General', 'description' => 'General maintenance and cleaning', 'icon' => 'tool']);

        // ─── Maintenance Requests ──────────────────────────────────

        $mReq1 = MaintenanceRequest::create([
            'unit_id' => $unit1->id, 'tenant_id' => $tenant1->id,
            'category_id' => $catPlumbing->id, 'assigned_to' => $manager1->id,
            'title' => 'Leaky kitchen faucet',
            'description' => 'The kitchen faucet has been dripping constantly for the past two days.',
            'priority' => 'medium', 'status' => 'in_progress',
            'estimated_cost' => 150.00, 'scheduled_date' => now()->addDays(2),
        ]);

        $mReq2 = MaintenanceRequest::create([
            'unit_id' => $unit2->id, 'tenant_id' => $tenant2->id,
            'category_id' => $catHvac->id,
            'title' => 'AC unit not cooling',
            'description' => 'The air conditioning unit is running but not producing cold air.',
            'priority' => 'high', 'status' => 'open',
            'estimated_cost' => 500.00,
        ]);

        MaintenanceRequest::create([
            'unit_id' => $unit1->id, 'tenant_id' => $tenant1->id,
            'category_id' => null,
            'title' => 'Broken window lock',
            'description' => 'The lock on the bedroom window is broken and cannot be secured.',
            'priority' => 'urgent', 'status' => 'open',
        ]);

        MaintenanceRequest::create([
            'unit_id' => $unit5->id, 'tenant_id' => $tenant3->id,
            'category_id' => $catAppliance->id,
            'title' => 'Garage door opener malfunction',
            'description' => 'The garage door remote stopped working. Replaced batteries but still not responding.',
            'priority' => 'low', 'status' => 'resolved',
            'actual_cost' => 85.00, 'resolved_at' => now()->subDays(3),
        ]);

        // ─── Maintenance Comments ──────────────────────────────────

        MaintenanceComment::create([
            'maintenance_request_id' => $mReq1->id, 'user_id' => $tenant1->id,
            'comment' => 'The dripping has gotten worse since yesterday. Water is pooling under the sink.',
            'is_internal' => false,
        ]);
        MaintenanceComment::create([
            'maintenance_request_id' => $mReq1->id, 'user_id' => $manager1->id,
            'comment' => 'Scheduled plumber visit for Thursday morning. Parts ordered.',
            'is_internal' => false,
        ]);
        MaintenanceComment::create([
            'maintenance_request_id' => $mReq1->id, 'user_id' => $manager1->id,
            'comment' => 'Vendor quote: $120 labor + $30 parts. Under budget.',
            'is_internal' => true,
        ]);
        MaintenanceComment::create([
            'maintenance_request_id' => $mReq2->id, 'user_id' => $tenant2->id,
            'comment' => 'Temperature inside is 85°F even though thermostat is set to 72°F.',
            'is_internal' => false,
        ]);

        // ─── Documents ─────────────────────────────────────────────

        Document::create([
            'uploaded_by' => $owner1->id, 'documentable_type' => Property::class, 'documentable_id' => $property1->id,
            'title' => 'Property Insurance Certificate', 'file_path' => 'documents/properties/insurance-cert-2026.pdf',
            'file_name' => 'insurance-cert-2026.pdf', 'mime_type' => 'application/pdf', 'file_size' => 245760,
            'category' => 'insurance',
        ]);
        Document::create([
            'uploaded_by' => $manager1->id, 'documentable_type' => Lease::class, 'documentable_id' => $lease1->id,
            'title' => 'Signed Lease Agreement — Unit 101', 'file_path' => 'documents/leases/lease-101-signed.pdf',
            'file_name' => 'lease-101-signed.pdf', 'mime_type' => 'application/pdf', 'file_size' => 512000,
            'category' => 'lease_agreement',
        ]);
        Document::create([
            'uploaded_by' => $tenant1->id, 'documentable_type' => User::class, 'documentable_id' => $tenant1->id,
            'title' => 'Tenant ID — Alice Tenant', 'file_path' => 'documents/users/alice-id.jpg',
            'file_name' => 'alice-id.jpg', 'mime_type' => 'image/jpeg', 'file_size' => 184320,
            'category' => 'id_document',
        ]);
        Document::create([
            'uploaded_by' => $manager1->id, 'documentable_type' => MaintenanceRequest::class, 'documentable_id' => $mReq1->id,
            'title' => 'Photo of leaky faucet', 'file_path' => 'documents/maintenance/faucet-leak.jpg',
            'file_name' => 'faucet-leak.jpg', 'mime_type' => 'image/jpeg', 'file_size' => 307200,
            'category' => 'photo',
        ]);
    }
}
