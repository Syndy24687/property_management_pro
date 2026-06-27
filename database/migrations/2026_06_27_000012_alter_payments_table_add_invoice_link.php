<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('lease_id')
                  ->constrained('invoices')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->after('status')
                  ->constrained('users')->nullOnDelete();
            $table->string('transaction_id')->nullable()->after('reference_number');

            $table->index('invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropForeign(['received_by']);
            $table->dropColumn(['invoice_id', 'received_by', 'transaction_id']);
        });
    }
};
