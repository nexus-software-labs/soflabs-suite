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
        Schema::table('payments', function (Blueprint $table) {
            // Campos de costos desglosados
            $table->decimal('subtotal_pre_alerta', 10, 2)->nullable()->after('amount')->comment('(flete + garantía + impuestos + cargos adicionales)');
            $table->decimal('costo_envio', 10, 2)->nullable()->after('subtotal_pre_alerta');
            $table->decimal('total', 10, 2)->nullable()->after('costo_envio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['subtotal_pre_alerta', 'costo_envio', 'total']);
        });
    }
};
