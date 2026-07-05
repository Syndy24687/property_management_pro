<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Lease;
use App\Models\SystemSetting;
use App\Models\UtilityCharge;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(
        protected SystemSettingService $settings
    ) {}

    /**
     * Get paginated invoices with filters.
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Invoice::with(['lease.unit.property', 'lease.tenant', 'items']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['lease_id'])) {
            $query->where('lease_id', $filters['lease_id']);
        }
        if (!empty($filters['from_date'])) {
            $query->whereDate('issue_date', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('issue_date', '<=', $filters['to_date']);
        }
        if (!empty($filters['tenant_id'])) {
            $query->whereHas('lease', fn($q) => $q->where('tenant_id', $filters['tenant_id']));
        }

        return $query->orderByDesc('issue_date')->paginate($perPage);
    }

    /**
     * Find an invoice by ID with relationships.
     */
    public function find(int $id): ?Invoice
    {
        return Invoice::with(['lease.unit.property', 'lease.tenant', 'items', 'payments'])->find($id);
    }

    /**
     * Generate a monthly rent invoice for a lease.
     */
    public function generateMonthlyInvoice(Lease $lease, ?string $billingMonth = null): Invoice
    {
        $billingMonth = $billingMonth ?? now()->format('Y-m');
        $prefix = $this->settings->getInvoicePrefix();
        $taxRate = $this->settings->getTaxRate();

        return DB::transaction(function () use ($lease, $billingMonth, $prefix, $taxRate) {
            // Create invoice
            $invoice = Invoice::create([
                'lease_id'       => $lease->id,
                'invoice_number' => $this->generateInvoiceNumber($prefix),
                'issue_date'     => now()->toDateString(),
                'due_date'       => now()->day($lease->payment_day_of_month ?? 1)->toDateString(),
                'status'         => 'draft',
            ]);

            // Add rent line item
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'type'       => 'rent',
                'description' => "Monthly Rent — {$billingMonth}",
                'quantity'    => 1,
                'unit_price'  => $lease->rent_amount,
                'amount'      => $lease->rent_amount,
            ]);

            // Add pending utility charges
            $pendingCharges = UtilityCharge::where('status', 'pending')
                ->whereHas('utilityMeter', fn($q) => $q->where('unit_id', $lease->unit_id))
                ->get();

            foreach ($pendingCharges as $charge) {
                $meterType = $charge->utilityMeter->utilityType->name ?? 'Utility';
                InvoiceItem::create([
                    'invoice_id'       => $invoice->id,
                    'type'             => 'utility',
                    'description'      => "{$meterType} — {$charge->billing_period_start->format('M Y')} ({$charge->usage} {$charge->utilityMeter->utilityType->unit_of_measure})",
                    'quantity'          => 1,
                    'unit_price'        => $charge->amount,
                    'amount'            => $charge->amount,
                    'utility_charge_id' => $charge->id,
                ]);

                $charge->update(['status' => 'invoiced']);
            }

            // Calculate totals
            $subtotal = $invoice->items()->sum('amount');
            $taxAmount = round($subtotal * ($taxRate / 100), 2);

            $invoice->update([
                'subtotal'     => $subtotal,
                'tax_amount'   => $taxAmount,
                'total_amount' => $subtotal + $taxAmount,
                'balance_due'  => $subtotal + $taxAmount,
            ]);

            return $invoice->fresh(['items']);
        });
    }

    /**
     * Generate a security deposit invoice when a lease is created.
     */
    public function generateDepositInvoice(Lease $lease): Invoice
    {
        $prefix = $this->settings->getInvoicePrefix();

        return DB::transaction(function () use ($lease, $prefix) {
            $invoice = Invoice::create([
                'lease_id'       => $lease->id,
                'invoice_number' => $this->generateInvoiceNumber($prefix),
                'issue_date'     => now()->toDateString(),
                'due_date'       => $lease->start_date->toDateString(),
                'subtotal'       => $lease->deposit_amount,
                'tax_amount'     => 0,
                'total_amount'   => $lease->deposit_amount,
                'balance_due'    => $lease->deposit_amount,
                'status'         => 'sent',
            ]);

            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'type'        => 'deposit',
                'description' => 'Security Deposit',
                'quantity'    => 1,
                'unit_price'  => $lease->deposit_amount,
                'amount'      => $lease->deposit_amount,
            ]);

            return $invoice;
        });
    }

    /**
     * Apply late fee to an overdue invoice.
     */
    public function applyLateFee(Invoice $invoice): void
    {
        $lease = $invoice->lease;
        $lateFee = $lease->late_fee_amount;

        // Fall back to system setting percentage if lease doesn't specify a fixed fee
        if ($lateFee <= 0) {
            $percentage = $this->settings->getLateFeePercentage();
            $lateFee = round($invoice->subtotal * ($percentage / 100), 2);
        }

        if ($lateFee <= 0) {
            return;
        }

        // Don't double-apply
        $hasLateFee = $invoice->items()->where('type', 'late_fee')->exists();
        if ($hasLateFee) {
            return;
        }

        InvoiceItem::create([
            'invoice_id'  => $invoice->id,
            'type'        => 'late_fee',
            'description' => 'Late Payment Fee',
            'quantity'    => 1,
            'unit_price'  => $lateFee,
            'amount'      => $lateFee,
        ]);

        $invoice->recalculateTotals();
    }

    /**
     * Mark invoice as sent.
     */
    public function markAsSent(Invoice $invoice): Invoice
    {
        $invoice->update(['status' => 'sent']);
        return $invoice;
    }

    /**
     * Void an invoice.
     */
    public function voidInvoice(Invoice $invoice): Invoice
    {
        $invoice->update(['status' => 'void']);

        // Release any invoiced utility charges back to pending
        $invoice->items()
            ->whereNotNull('utility_charge_id')
            ->each(function ($item) {
                UtilityCharge::where('id', $item->utility_charge_id)->update(['status' => 'pending']);
            });

        return $invoice;
    }

    /**
     * Record a payment against an invoice.
     */
    public function recordPayment(Invoice $invoice, float $amount): void
    {
        $newPaid = $invoice->amount_paid + $amount;
        $newBalance = $invoice->total_amount - $newPaid;

        $status = $newBalance <= 0 ? 'paid' : 'partially_paid';

        $invoice->update([
            'amount_paid' => $newPaid,
            'balance_due' => max(0, $newBalance),
            'status'      => $status,
        ]);
    }

    /**
     * Generate a unique invoice number.
     */
    protected function generateInvoiceNumber(string $prefix): string
    {
        $year = now()->format('Y');
        $lastInvoice = Invoice::where('invoice_number', 'like', "{$prefix}-{$year}-%")
            ->orderByDesc('id')
            ->first();

        $sequence = 1;
        if ($lastInvoice) {
            $parts = explode('-', $lastInvoice->invoice_number);
            $sequence = ((int) end($parts)) + 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $year, $sequence);
    }

    /**
     * Create a manual invoice.
     */
    public function createManual(array $data): Invoice
    {
        $prefix = $this->settings->getInvoicePrefix();
        $taxRate = $this->settings->getTaxRate();

        return DB::transaction(function () use ($data, $prefix, $taxRate) {
            $invoice = Invoice::create([
                'lease_id'       => $data['lease_id'],
                'invoice_number' => $this->generateInvoiceNumber($prefix),
                'issue_date'     => $data['issue_date'] ?? now()->toDateString(),
                'due_date'       => $data['due_date'],
                'status'         => $data['status'] ?? 'draft',
                'notes'          => $data['notes'] ?? null,
            ]);

            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $amount = ($item['quantity'] ?? 1) * $item['unit_price'];
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'type'        => $item['type'] ?? 'other',
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'] ?? 1,
                    'unit_price'  => $item['unit_price'],
                    'amount'      => $amount,
                ]);
                $subtotal += $amount;
            }

            $taxAmount = round($subtotal * ($taxRate / 100), 2);
            $invoice->update([
                'subtotal'     => $subtotal,
                'tax_amount'   => $taxAmount,
                'total_amount' => $subtotal + $taxAmount,
                'balance_due'  => $subtotal + $taxAmount,
            ]);

            return $invoice->fresh(['items']);
        });
    }
}
