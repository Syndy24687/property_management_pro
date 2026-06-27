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
        Schema::create('maintenance_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('tenant_id')
                  ->constrained('maintenance_categories')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->after('status')
                  ->constrained('users')->nullOnDelete();
            $table->decimal('estimated_cost', 10, 2)->nullable()->after('assigned_to');
            $table->decimal('actual_cost', 10, 2)->nullable()->after('estimated_cost');
            $table->timestamp('scheduled_date')->nullable()->after('actual_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['assigned_to']);
            $table->dropColumn(['category_id', 'assigned_to', 'estimated_cost', 'actual_cost', 'scheduled_date']);
        });

        Schema::dropIfExists('maintenance_categories');
    }
};
