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
        Schema::create('inventory_outbound_request_lines', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id');
            $table->foreignUlid('outbound_request_id')->constrained('inventory_outbound_requests')->cascadeOnDelete();
            $table->unsignedInteger('line_number');
            $table->foreignUlid('product_id')->constrained('inventory_products')->cascadeOnDelete();
            $table->decimal('requested_quantity', 16, 4);
            $table->decimal('reserved_quantity', 16, 4)->default(0);
            $table->decimal('dispatched_quantity', 16, 4)->default(0);
            $table->string('status', 30)->default('requested');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'outbound_request_id', 'line_number'], 'inventory_outbound_request_lines_unique');
            $table->index(['tenant_id', 'product_id', 'status'], 'inventory_outbound_request_lines_lookup_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_outbound_request_lines');
    }
};
