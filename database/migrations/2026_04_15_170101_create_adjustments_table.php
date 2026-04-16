<?php

declare(strict_types=1);

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
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id');
            $table->foreignUlid('movement_id')->constrained('inventory_movements')->cascadeOnDelete();
            $table->foreignUlid('product_id')->constrained('inventory_products')->cascadeOnDelete();
            $table->foreignUlid('warehouse_id')->constrained('inventory_warehouses')->cascadeOnDelete();
            $table->string('adjustment_type', 40);
            $table->decimal('difference_quantity', 16, 4);
            $table->string('reason', 120);
            $table->string('evidence_path', 255)->nullable();
            $table->string('notes', 500)->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('adjusted_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'product_id', 'warehouse_id', 'adjusted_at'], 'inventory_adjustments_lookup_idx');
            $table->index(['tenant_id', 'reason']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');
    }
};
