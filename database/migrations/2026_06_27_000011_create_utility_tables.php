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
        Schema::create('utility_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unit_of_measure');
            $table->decimal('default_rate', 8, 4);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('utility_meters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->foreignId('utility_type_id')->constrained('utility_types')->cascadeOnDelete();
            $table->string('meter_number');
            $table->date('installation_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['unit_id', 'utility_type_id']);
        });

        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utility_meter_id')->constrained('utility_meters')->cascadeOnDelete();
            $table->foreignId('read_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('reading_date');
            $table->decimal('reading_value', 12, 2);
            $table->decimal('previous_value', 12, 2);
            $table->decimal('usage', 12, 2);
            $table->string('photo_path')->nullable();
            $table->timestamps();

            $table->index('reading_date');
        });

        Schema::create('utility_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utility_meter_id')->constrained('utility_meters')->cascadeOnDelete();
            $table->foreignId('meter_reading_id')->nullable()->constrained('meter_readings')->nullOnDelete();
            $table->date('billing_period_start');
            $table->date('billing_period_end');
            $table->decimal('usage', 12, 2);
            $table->decimal('rate', 8, 4);
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'invoiced', 'paid'])->default('pending');
            $table->timestamps();

            $table->index('status');
            $table->index('billing_period_start');
        });

        // Now add the FK constraint on invoice_items.utility_charge_id
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreign('utility_charge_id')->references('id')->on('utility_charges')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['utility_charge_id']);
        });

        Schema::dropIfExists('utility_charges');
        Schema::dropIfExists('meter_readings');
        Schema::dropIfExists('utility_meters');
        Schema::dropIfExists('utility_types');
    }
};
