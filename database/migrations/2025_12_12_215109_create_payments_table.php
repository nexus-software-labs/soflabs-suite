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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable()->index();

            // Relación polimórfica: puede asociarse a cualquier modelo (PrintOrder, PreAlertOrder, etc.)
            $table->morphs('paymentable');

            // Gateway y método de pago
            $table->string('gateway')->comment('cybersource, cash, transfer, etc.');
            $table->string('payment_method')->nullable()->comment('card, cash, transfer, etc.');

            // Estado del pago
            $table->enum('status', [
                'pending',      // Pago iniciado, esperando procesamiento
                'processing',   // En proceso (para algunos gateways)
                'completed',    // Pago exitoso
                'failed',       // Pago fallido
                'cancelled',    // Pago cancelado
                'refunded',      // Pago reembolsado
            ])->default('pending');

            // Información financiera
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');

            // Identificadores de transacción
            $table->string('reference_number')->unique()->comment('Número de referencia único del pago');
            $table->string('transaction_id')->nullable()->comment('ID de transacción del gateway');
            $table->string('transaction_uuid')->nullable()->comment('UUID único para CyberSource');

            // Información del cliente
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();

            // Dirección de facturación (para CyberSource)
            $table->text('billing_address')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_country', 2)->nullable();
            $table->string('billing_postal_code')->nullable();

            // Metadatos adicionales (JSON)
            $table->json('metadata')->nullable()->comment('Datos adicionales del gateway');
            $table->json('gateway_response')->nullable()->comment('Respuesta completa del gateway');

            // URLs y redirecciones
            $table->text('redirect_url')->nullable()->comment('URL para redirigir al usuario');
            $table->text('return_url')->nullable()->comment('URL de retorno después del pago');
            $table->text('cancel_url')->nullable()->comment('URL si el usuario cancela');

            // Información de firma (para CyberSource)
            $table->text('signature')->nullable()->comment('Firma HMAC del pago');
            $table->text('signed_field_names')->nullable();

            // Decisiones y códigos del gateway
            $table->string('decision')->nullable()->comment('ACCEPT, REJECT, REVIEW, etc.');
            $table->string('reason_code')->nullable()->comment('Código de razón del gateway');
            $table->text('reason_message')->nullable()->comment('Mensaje de razón del gateway');

            // Timestamps
            $table->timestamp('processed_at')->nullable()->comment('Fecha de procesamiento');
            $table->timestamp('completed_at')->nullable()->comment('Fecha de completado');
            $table->timestamp('failed_at')->nullable()->comment('Fecha de fallo');
            $table->timestamps();
            $table->softDeletes();

            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            // Índices para mejorar búsquedas
            $table->index('status');
            $table->index('gateway');
            $table->index('user_id');
            $table->index('transaction_id');
            $table->index('reference_number');
            $table->index('created_at');

            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
