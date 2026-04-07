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
        Schema::create('payment_details', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable()->index();

            // Relación con payment
            $table->foreignId('payment_id')
                ->constrained('payments')
                ->onDelete('cascade');

            // Costos desglosados de la calculadora
            // Costo de flete (peso × valor por libra)
            $table->decimal('flete', 10, 2)->default(0);
            // Garantía de entrega (porcentaje del valor declarado)
            $table->decimal('garantia_entrega', 10, 2)->default(0);
            // IVA-CIF (porcentaje del valor declarado)
            $table->decimal('iva_cif', 10, 2)->default(0);
            // DAI (Derecho Arancelario de Importación) si aplica
            $table->decimal('dai', 10, 2)->default(0);
            // Total de impuestos (iva_cif + dai)
            $table->decimal('total_impuestos', 10, 2)->default(0);
            // Gestión aduanal
            $table->decimal('gestion_aduanal', 10, 2)->default(0);
            // Manejo de terceros
            $table->decimal('manejo_terceros', 10, 2)->default(0);

            // Información adicional para referencia
            $table->decimal('weight_lbs', 8, 2)->nullable();
            $table->decimal('value_declared', 10, 2)->nullable();
            $table->decimal('valor_por_libra', 10, 2)->nullable();
            $table->decimal('dai_percentage', 5, 2)->nullable();
            $table->boolean('aplica_dai')->default(false);
            $table->decimal('umbral_dai', 10, 2)->nullable();

            $table->timestamps();

            // Índices
            $table->index('payment_id');

            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_details');
    }
};
