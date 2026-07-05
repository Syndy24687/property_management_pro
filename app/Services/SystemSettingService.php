<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SystemSettingService
{
    /**
     * Get a single setting value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return SystemSetting::getValue($key, $default);
    }

    /**
     * Set a single setting value.
     */
    public function set(string $key, mixed $value): void
    {
        SystemSetting::setValue($key, $value);
    }

    /**
     * Get all settings for a group.
     */
    public function getGroup(string $group): array
    {
        return SystemSetting::getGroup($group);
    }

    /**
     * Get all settings, grouped.
     */
    public function getAll(): array
    {
        return Cache::remember('settings.all', 3600, function () {
            $settings = SystemSetting::all();
            $grouped = [];

            foreach ($settings as $setting) {
                $grouped[$setting->group][$setting->key] = [
                    'value'       => SystemSetting::getValue($setting->key),
                    'type'        => $setting->type,
                    'description' => $setting->description,
                ];
            }

            return $grouped;
        });
    }

    /**
     * Bulk update settings.
     */
    public function bulkUpdate(array $settings): void
    {
        foreach ($settings as $key => $value) {
            SystemSetting::setValue($key, $value);
        }

        Cache::forget('settings.all');
    }

    // ─── Convenience Helpers ────────────────────────────────────────

    public function getCurrency(): string
    {
        return $this->get('currency', 'USD');
    }

    public function getCurrencySymbol(): string
    {
        return $this->get('currency_symbol', '$');
    }

    public function getTaxRate(): float
    {
        return (float) $this->get('tax_rate', 0);
    }

    public function getLateFeePercentage(): float
    {
        return (float) $this->get('late_fee_percentage', 5);
    }

    public function getGracePeriodDays(): int
    {
        return (int) $this->get('grace_period_days', 5);
    }

    public function getInvoicePrefix(): string
    {
        return $this->get('invoice_prefix', 'INV');
    }

    public function getDateFormat(): string
    {
        return $this->get('date_format', 'Y-m-d');
    }

    public function getLanguage(): string
    {
        return $this->get('language', 'en');
    }
}
