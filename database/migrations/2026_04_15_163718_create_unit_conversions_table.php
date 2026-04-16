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
        Schema::create('inventory_unit_conversions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id');
            $table->foreignUlid('from_unit_id')->constrained('inventory_units')->cascadeOnDelete();
            $table->foreignUlid('to_unit_id')->constrained('inventory_units')->cascadeOnDelete();
            $table->decimal('factor', 20, 8);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'from_unit_id', 'to_unit_id'], 'inventory_unit_conversions_unique');
            $table->index(['tenant_id', 'from_unit_id']);
            $table->index(['tenant_id', 'to_unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_unit_conversions');
    }
};
