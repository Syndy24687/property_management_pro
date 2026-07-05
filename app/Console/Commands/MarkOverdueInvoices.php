<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Console\Command;

class MarkOverdueInvoices extends Command
{
    protected $signature = 'invoices:mark-overdue';
    protected $description = 'Mark unpaid invoices as overdue and apply late fees after grace period';

    public function handle(InvoiceService $invoiceService): int
    {
        $this->info('Checking for overdue invoices...');

        $overdueInvoices = Invoice::whereIn('status', ['sent', 'partially_paid'])
            ->where('due_date', '<', now()->toDateString())
            ->with('lease')
            ->get();

        $marked = 0;

        foreach ($overdueInvoices as $invoice) {
            $gracePeriod = $invoice->lease->grace_period_days ?? 5;
            $daysOverdue = now()->diffInDays($invoice->due_date);

            if ($daysOverdue > $gracePeriod) {
                $invoice->update(['status' => 'overdue']);
                $invoiceService->applyLateFee($invoice);
                $marked++;
                $this->line("  ✓ {$invoice->invoice_number} — {$daysOverdue} days overdue, late fee applied");
            }
        }

        $this->info("Done. Marked {$marked} invoice(s) as overdue.");

        return self::SUCCESS;
    }
}
