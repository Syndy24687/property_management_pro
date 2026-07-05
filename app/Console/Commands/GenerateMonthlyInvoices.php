<?php

namespace App\Console\Commands;

use App\Models\Lease;
use App\Services\InvoiceService;
use Illuminate\Console\Command;

class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'invoices:generate {--month= : Billing month in Y-m format}';
    protected $description = 'Generate monthly invoices for all active leases';

    public function handle(InvoiceService $invoiceService): int
    {
        $billingMonth = $this->option('month') ?? now()->format('Y-m');

        $this->info("Generating invoices for {$billingMonth}...");

        $leases = Lease::where('status', 'active')->with('unit')->get();
        $generated = 0;
        $skipped = 0;

        foreach ($leases as $lease) {
            // Check if invoice already exists for this month
            $exists = $lease->invoices()
                ->where('invoice_number', 'like', "%-{$billingMonth}%")
                ->whereHas('items', fn($q) => $q->where('type', 'rent'))
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            try {
                $invoice = $invoiceService->generateMonthlyInvoice($lease, $billingMonth);
                $invoiceService->markAsSent($invoice);
                $generated++;
                $this->line("  ✓ {$invoice->invoice_number} for Lease #{$lease->id}");
            } catch (\Throwable $e) {
                $this->error("  ✗ Lease #{$lease->id}: {$e->getMessage()}");
            }
        }

        $this->info("Done. Generated: {$generated}, Skipped: {$skipped}");

        return self::SUCCESS;
    }
}
