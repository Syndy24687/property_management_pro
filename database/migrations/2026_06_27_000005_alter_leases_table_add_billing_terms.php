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
        Schema::table('leases', function (Blueprint $table) {
            $table->enum('payment_frequency', ['monthly', 'weekly', 'biweekly', 'quarterly', 'annually'])
                  ->default('monthly')->after('deposit_amount');
            $table->unsignedTinyInteger('payment_day_of_month')->default(1)->after('payment_frequency');
            $table->decimal('late_fee_amount', 10, 2)->default(0)->after('payment_day_of_month');
            $table->unsignedTinyInteger('grace_period_days')->default(5)->after('late_fee_amount');
            $table->boolean('auto_renew')->default(false)->after('grace_period_days');
            $table->text('terms')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn([
                'payment_frequency',
                'payment_day_of_month',
                'late_fee_amount',
                'grace_period_days',
                'auto_renew',
                'terms',
            ]);
        });
    }
};
