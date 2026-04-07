<?php

namespace Database\Factories;

use App\Models\Printing\PrintOrder;
use App\Models\Printing\PrintOrderHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrintOrderHistory>
 */
class PrintOrderHistoryFactory extends Factory
{
    protected $model = PrintOrderHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement([
            'pending',
            'payment_pending',
            'in_queue',
            'printing',
            'ready',
            'shipped',
            'delivered',
            'cancelled',
        ]);

        $comments = [
            'pending' => [
                'Pedido recibido correctamente',
                'Nueva orden de impresión registrada',
                'Orden creada por el cliente',
            ],
            'payment_pending' => [
                'Esperando confirmación de pago',
                'Pago pendiente de verificación',
                'Cliente notificado para realizar el pago',
            ],
            'in_queue' => [
                'Pago confirmado, agregado a la cola de impresión',
                'Orden agregada a la cola',
                'En espera de impresión',
            ],
            'printing' => [
                'Impresión en proceso',
                'Comenzó la impresión del documento',
                'Impresora asignada, procesando páginas',
            ],
            'ready' => [
                'Impresión completada, listo para recoger',
                'Documento listo, cliente notificado',
                'Esperando que el cliente recoja su pedido',
            ],
            'shipped' => [
                'Pedido en camino al cliente',
                'Enviado con mensajería',
                'En ruta de entrega',
            ],
            'delivered' => [
                'Pedido entregado exitosamente',
                'Cliente confirmó recepción',
                'Entrega completada',
            ],
            'cancelled' => [
                'Pedido cancelado por el cliente',
                'Cancelación solicitada',
                'Orden cancelada por falta de pago',
                'Cliente solicitó la cancelación',
            ],
        ];

        $selectedComments = $comments[$status] ?? ['Actualización de estado'];

        return [
            'print_order_id' => PrintOrder::factory(),
            'status' => $status,
            'comment' => $this->faker->boolean(70)
                ? $this->faker->randomElement($selectedComments)
                : null,
            'created_by' => $this->faker->boolean(80) ? User::factory() : null,
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Estado específico
     */
    public function forStatus(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }

    /**
     * Con comentario específico
     */
    public function withComment(string $comment): static
    {
        return $this->state(fn (array $attributes) => [
            'comment' => $comment,
        ]);
    }

    /**
     * Sin comentario
     */
    public function withoutComment(): static
    {
        return $this->state(fn (array $attributes) => [
            'comment' => null,
        ]);
    }

    /**
     * Creado por admin específico
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
        ]);
    }
}
