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
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('name', 100); // "Casa", "Oficina"

            $table->string('country_code', 2)->nullable(); // Código del país (SV, US, etc.)
            $table->string('country', 2)->nullable(); // Código del país (SV, US, etc.)
            $table->string('region_code', 50)->nullable();
            $table->string('region', 100)->nullable(); // Región, estado o departamento
            $table->string('city_code', 50)->nullable();
            $table->string('city', 100)->nullable(); // Ciudad
            $table->string('locality_code', 50)->nullable();
            $table->string('locality', 100)->nullable(); // Localidad o barrio

            $table->text('address')->nullable(); // Dirección completa

            $table->text('references')->nullable(); // Referencias adicionales
            $table->decimal('latitude', 10, 8)->nullable(); // Coordenadas
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('phone', 20)->nullable(); // Teléfono específico de esta dirección
            $table->boolean('is_default')->default(false); // Dirección por defecto
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');

            $table->index('customer_id');
            $table->index('is_default');

            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
