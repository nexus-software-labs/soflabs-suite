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
        Schema::create('inventory_supplier_products', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id');
            $table->foreignUlid('supplier_id')->constrained('inventory_suppliers')->cascadeOnDelete();
            $table->foreignUlid('product_id')->constrained('inventory_products')->cascadeOnDelete();
            $table->decimal('price', 16, 4)->nullable();
            $table->foreignUlid('unit_id')->nullable()->constrained('inventory_units')->nullOnDelete();
            $table->string('supplier_sku', 50)->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'supplier_id', 'product_id'], 'inventory_supplier_products_unique');
            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_supplier_products');
    }
};
