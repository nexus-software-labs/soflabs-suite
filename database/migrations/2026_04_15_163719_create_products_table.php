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
        Schema::create('inventory_products', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id');
            $table->foreignUlid('group_id')->constrained('inventory_groups')->cascadeOnDelete();
            $table->foreignUlid('brand_id')->nullable()->constrained('inventory_brands')->nullOnDelete();
            $table->string('sku', 50);
            $table->string('name', 200);
            $table->foreignUlid('purchase_unit_id')->constrained('inventory_units')->cascadeOnDelete();
            $table->foreignUlid('stock_unit_id')->constrained('inventory_units')->cascadeOnDelete();
            $table->foreignUlid('sales_unit_id')->nullable()->constrained('inventory_units')->nullOnDelete();
            $table->string('valuation_method', 30)->default('fifo');
            $table->decimal('minimum_stock', 16, 4)->nullable();
            $table->string('status', 30)->default('active');
            $table->text('embedding')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'sku']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_products');
    }
};
