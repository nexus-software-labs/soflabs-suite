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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id');
            $table->foreignUlid('product_id')->constrained('inventory_products')->cascadeOnDelete();
            $table->foreignUlid('warehouse_id')->constrained('inventory_warehouses')->cascadeOnDelete();
            $table->string('movement_type', 40);
            $table->decimal('quantity', 16, 4);
            $table->decimal('stock_before', 16, 4);
            $table->decimal('stock_after', 16, 4);
            $table->string('reference_type', 80)->nullable();
            $table->string('reference_id', 80)->nullable();
            $table->string('notes', 500)->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moved_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'product_id', 'warehouse_id', 'moved_at'], 'inventory_movements_kardex_idx');
            $table->index(['tenant_id', 'movement_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
