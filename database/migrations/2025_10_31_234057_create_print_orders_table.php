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
        Schema::create('print_orders', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->string('order_number')->unique(); // IMP-00123
            $table->foreignId('user_id')->nullable()->constrained('users'); // Cliente registrado (opcional)
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();

            // Estado del pedido
            $table->enum('status', [
                'pending',          // Pedido recibido
                'payment_pending',  // Esperando pago
                'in_queue',         // En cola de impresión
                'printing',         // Imprimiendo
                'ready',            // Listo para recoger
                'shipped',          // En camino (delivery)
                'delivered',        // Entregado
                'cancelled',         // Cancelado
            ])->default('pending');

            // Especificaciones de impresión
            $table->enum('print_type', ['color', 'bw'])->default('bw'); // blanco y negro
            $table->enum('paper_size', ['letter', 'legal', 'a4', 'double_letter'])->default('letter'); // 🔥 Agregado double_letter
            $table->boolean('is_plan')->default(false);
            $table->enum('paper_type', ['bond', 'photo_glossy'])->default('bond'); // 🔥 NUEVO: Tipo de papel
            $table->string('page_range')->default('all'); // "all", "1-5", "1,3,5"
            $table->enum('orientation', ['portrait', 'landscape'])->default('portrait');
            $table->integer('copies')->default(1);
            $table->boolean('binding')->default(false); // Engargolado
            $table->boolean('double_sided')->default(false); // Doble cara
            $table->integer('pages_count'); // Total de páginas a imprimir

            // Entrega
            $table->enum('delivery_method', ['pickup', 'delivery'])->default('pickup');
            $table->ulid('branch_id')->nullable();
            $table->foreignId('customer_address_id')
                ->nullable()
                ->constrained('customer_addresses')
                ->onDelete('set null');
            $table->text('delivery_address')->nullable();
            $table->string('delivery_phone')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->decimal('delivery_cost', 8, 2)->default(0);

            // Pago
            $table->enum('payment_method', [
                'cash',      // Efectivo
                'card',      // Tarjeta
                'transfer',  // Transferencia
                'paypal',     // PayPal
            ])->default('cash');

            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->decimal('subtotal', 8, 2); // Costo de impresión
            $table->decimal('tax', 8, 2)->default(0);
            $table->decimal('total', 8, 2); // Total con envío

            $table->foreignId('promotion_id')
                ->nullable()
                ->constrained('promotions')
                ->nullOnDelete();

            // Descuento aplicado al subtotal
            $table->decimal('discount', 8, 2)
                ->default(0)
                ->comment('Descuento aplicado al subtotal por promoción');

            // Descuento aplicado al costo de envío
            $table->decimal('delivery_discount', 8, 2)
                ->default(0)
                ->comment('Descuento aplicado al costo de envío por promoción');

            // Precios guardados al momento de la orden (importante para historial)
            $table->decimal('price_per_page', 8, 2); // Precio que se aplicó
            $table->decimal('binding_price', 8, 2)->default(0); // Precio engargolado aplicado
            $table->decimal('double_sided_cost', 8, 2)->default(0);

            // Notas adicionales
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();

            // Timestamps importantes
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');

            // Índices para mejorar búsquedas
            $table->index('status');
            $table->index('delivery_method');
            $table->index('promotion_id');
            $table->index('payment_status');
            $table->index('created_at');

            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_orders');
    }
};
