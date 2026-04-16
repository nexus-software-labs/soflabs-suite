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
        Schema::create('inventory_intake_documents', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id');
            $table->foreignUlid('supplier_id')->nullable()->constrained('inventory_suppliers')->nullOnDelete();
            $table->foreignUlid('warehouse_id')->constrained('inventory_warehouses')->cascadeOnDelete();
            $table->string('document_number', 80)->nullable();
            $table->date('document_date')->nullable();
            $table->string('currency_code', 10)->default('USD');
            $table->decimal('subtotal', 16, 4)->nullable();
            $table->decimal('tax', 16, 4)->nullable();
            $table->decimal('total', 16, 4)->nullable();
            $table->string('status', 30)->default('received');
            $table->string('origin', 30)->default('manual');
            $table->string('source_file_path', 255)->nullable();
            $table->decimal('ai_confidence', 5, 4)->nullable();
            $table->json('warnings')->nullable();
            $table->json('raw_extraction')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('rejection_reason', 500)->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status', 'origin']);
            $table->index(['tenant_id', 'warehouse_id', 'created_at'], 'inventory_intake_documents_lookup_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_intake_documents');
    }
};
