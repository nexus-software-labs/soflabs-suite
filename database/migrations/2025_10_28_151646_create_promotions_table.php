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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->string('name');
            $table->text('description');

            // Tipo de aplicación
            $table->enum('application_type', ['automatic', 'coupon'])->default('automatic');
            $table->string('coupon_code')->nullable()->unique();
            $table->integer('usage_limit')->nullable(); // Usos totales del cupón
            $table->integer('times_used')->default(0);

            // Tipo de descuento
            $table->enum('discount_type', ['free_delivery', 'percentage', 'fixed_amount']);
            $table->decimal('discount_value', 8, 2)->nullable();
            $table->enum('applies_to', ['delivery', 'subtotal'])->default('delivery');

            // Alcance geográfico
            $table->enum('scope_type', ['all', 'region', 'country', 'branches', 'customers']);
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();

            // Servicios
            $table->enum('service_type', ['both', 'print_order', 'pre_alert'])->default('both');

            // Restricciones
            $table->decimal('min_order_amount', 8, 2)->nullable();
            $table->decimal('max_discount_amount', 8, 2)->nullable();

            // Vigencia
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');

            // Índices
            $table->index(['active', 'starts_at', 'expires_at']);
            $table->index('scope_type');
            $table->index('coupon_code');

            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
        });

        Schema::create('promotion_applications', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->morphs('applicable');
            $table->decimal('original_amount', 8, 2);
            $table->decimal('discount_amount', 8, 2);
            $table->string('applied_to');
            $table->timestamp('applied_at');
            $table->timestamps();

            $table->index(['promotion_id', 'applied_at']);

            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_applications');
        Schema::dropIfExists('promotions');
    }
};
