<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ─── General ────────────────────────────────────────
            ['group' => 'general', 'key' => 'app_name',  'value' => 'Property Management Pro', 'type' => 'string',  'description' => 'Application name'],
            ['group' => 'general', 'key' => 'timezone',  'value' => 'UTC',                     'type' => 'string',  'description' => 'System timezone'],

            // ─── Billing ────────────────────────────────────────
            ['group' => 'billing', 'key' => 'currency',             'value' => 'USD',    'type' => 'string',  'description' => 'Currency code (ISO 4217)'],
            ['group' => 'billing', 'key' => 'currency_symbol',      'value' => '$',      'type' => 'string',  'description' => 'Currency display symbol'],
            ['group' => 'billing', 'key' => 'tax_rate',             'value' => '0',      'type' => 'float',   'description' => 'Default tax percentage'],
            ['group' => 'billing', 'key' => 'late_fee_percentage',  'value' => '5',      'type' => 'float',   'description' => 'Late fee as % of rent'],
            ['group' => 'billing', 'key' => 'grace_period_days',    'value' => '5',      'type' => 'integer', 'description' => 'Days before late fee applies'],
            ['group' => 'billing', 'key' => 'invoice_prefix',       'value' => 'INV',    'type' => 'string',  'description' => 'Invoice number prefix'],
            ['group' => 'billing', 'key' => 'payment_methods',      'value' => '["bank_transfer","cash","online","check","credit_card"]', 'type' => 'json', 'description' => 'Enabled payment methods'],

            // ─── Localization ───────────────────────────────────
            ['group' => 'localization', 'key' => 'language',       'value' => 'en',        'type' => 'string', 'description' => 'Default language'],
            ['group' => 'localization', 'key' => 'date_format',    'value' => 'Y-m-d',     'type' => 'string', 'description' => 'Date display format'],
            ['group' => 'localization', 'key' => 'number_format',  'value' => '1,234.56',  'type' => 'string', 'description' => 'Number display format'],

            // ─── Notifications ──────────────────────────────────
            ['group' => 'notifications', 'key' => 'email_enabled',              'value' => 'true',  'type' => 'boolean', 'description' => 'Enable email notifications'],
            ['group' => 'notifications', 'key' => 'maintenance_notify_tenant',  'value' => 'true',  'type' => 'boolean', 'description' => 'Notify tenant on maintenance status change'],
            ['group' => 'notifications', 'key' => 'payment_notify_receipt',     'value' => 'true',  'type' => 'boolean', 'description' => 'Send payment receipt notification'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
