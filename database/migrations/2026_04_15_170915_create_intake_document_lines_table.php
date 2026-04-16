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
        Schema::create('inventory_intake_document_lines', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id');
            $table->foreignUlid('intake_document_id')->constrained('inventory_intake_documents')->cascadeOnDelete();
            $table->unsignedInteger('line_number');
            $table->foreignUlid('product_id')->nullable()->constrained('inventory_products')->nullOnDelete();
            $table->string('description_original', 255);
            $table->decimal('quantity', 16, 4);
            $table->foreignUlid('unit_id')->nullable()->constrained('inventory_units')->nullOnDelete();
            $table->decimal('unit_price', 16, 4)->nullable();
            $table->decimal('subtotal', 16, 4)->nullable();
            $table->boolean('linked_manually')->default(false);
            $table->string('status', 30)->default('pending_review');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'intake_document_id', 'line_number'], 'inventory_intake_document_lines_unique');
            $table->index(['tenant_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_intake_document_lines');
    }
};
