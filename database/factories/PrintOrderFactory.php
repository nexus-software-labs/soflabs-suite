<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Printing\PrintOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrintOrder>
 */
class PrintOrderFactory extends Factory
{
    protected $model = PrintOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $printType = $this->faker->randomElement(['color', 'bw']);
        $paperSize = $this->faker->randomElement(['letter', 'legal', 'a4', 'double_letter']);
        $isPlan = ($paperSize === 'double_letter');
        $paperType = $this->faker->randomElement(['bond', 'photo_glossy']);
        $copies = $this->faker->numberBetween(1, 10);
        $pagesCount = $this->faker->numberBetween(1, 150);
        $binding = $this->faker->boolean(30); // 30% de probabilidad
        $doubleSided = $this->faker->boolean(40); // 40% de probabilidad
        $deliveryMethod = $this->faker->randomElement(['pickup', 'delivery']);

        // Precios base
        $pricePerPage = match ($printType) {
            'color' => match ($paperType) {
                'bond' => 0.35,
                'photo_glossy' => 0.75,
            },
            'bw' => match ($paperType) {
                'bond' => 0.10,
                'photo_glossy' => 0.35,
            },
        };

        $bindingPrice = $binding ? 3.50 : 0;
        $doubleSidedCost = $doubleSided ? ($pagesCount * 0.03) : 0;

        $subtotal = ($pagesCount * $copies * $pricePerPage) + ($bindingPrice * $copies) + $doubleSidedCost;
        $tax = $subtotal * 0.13;
        $deliveryCost = $deliveryMethod === 'delivery' ? $this->faker->randomFloat(2, 2.50, 8.00) : 0;
        $total = $subtotal + $tax + $deliveryCost;

        $status = $this->faker->randomElement([
            'pending', 'payment_pending', 'in_queue',
            'printing', 'ready', 'shipped', 'delivered', 'cancelled',
        ]);

        $paymentStatus = match ($status) {
            'pending', 'payment_pending' => 'pending',
            'cancelled' => $this->faker->randomElement(['pending', 'failed']),
            default => 'paid',
        };

        $createdAt = $this->faker->dateTimeBetween('-6 months', 'now');
        $paidAt = in_array($status, ['in_queue', 'printing', 'ready', 'shipped', 'delivered'])
            ? $this->faker->dateTimeBetween($createdAt, 'now')
            : null;
        $printedAt = in_array($status, ['ready', 'shipped', 'delivered'])
            ? $this->faker->dateTimeBetween($paidAt ?? $createdAt, 'now')
            : null;
        $readyAt = in_array($status, ['ready', 'shipped', 'delivered'])
            ? $this->faker->dateTimeBetween($printedAt ?? $createdAt, 'now')
            : null;
        $deliveredAt = $status === 'delivered'
            ? $this->faker->dateTimeBetween($readyAt ?? $createdAt, 'now')
            : null;

        return [
            'order_number' => 'IMP-'.str_pad($this->faker->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'user_id' => $this->faker->boolean(70) ? User::factory() : null, // 70% tiene usuario
            'customer_name' => $this->faker->name(),
            'customer_email' => $this->faker->boolean(80) ? $this->faker->safeEmail() : null,
            'customer_phone' => $this->faker->boolean(90) ? $this->faker->phoneNumber() : null,

            'status' => $status,

            'print_type' => $printType,
            'paper_size' => $paperSize,
            'is_plan' => $isPlan,
            'paper_type' => $paperType,
            'page_range' => $this->generatePageRange($pagesCount),
            'orientation' => $this->faker->randomElement(['portrait', 'landscape']),
            'copies' => $copies,
            'binding' => $binding,
            'double_sided' => $doubleSided,
            'pages_count' => $pagesCount,

            'delivery_method' => $deliveryMethod,
            'branch_id' => $deliveryMethod === 'pickup' ? Branch::factory() : null,
            'delivery_address' => $deliveryMethod === 'delivery' ? $this->faker->address() : null,
            'delivery_phone' => $deliveryMethod === 'delivery' ? $this->faker->phoneNumber() : null,
            'delivery_notes' => $this->faker->boolean(40) ? $this->faker->sentence() : null,
            'delivery_cost' => $deliveryCost,

            'payment_method' => $this->faker->randomElement(['cash', 'card', 'transfer', 'paypal']),
            'payment_status' => $paymentStatus,
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'total' => round($total, 2),

            'price_per_page' => $pricePerPage,
            'binding_price' => $bindingPrice,
            'double_sided_cost' => round($doubleSidedCost, 2),

            'customer_notes' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
            'admin_notes' => $this->faker->boolean(20) ? $this->faker->sentence() : null,

            'paid_at' => $paidAt,
            'printed_at' => $printedAt,
            'ready_at' => $readyAt,
            'delivered_at' => $deliveredAt,

            'created_at' => $createdAt,
            'updated_at' => $this->faker->dateTimeBetween($createdAt, 'now'),

            'created_by' => User::factory(),
            'updated_by' => $this->faker->boolean(60) ? User::factory() : null,
        ];
    }

    /**
     * Genera un rango de páginas realista
     */
    private function generatePageRange(int $totalPages): string
    {
        $options = ['all', 'range', 'specific'];
        $choice = $this->faker->randomElement($options);

        return match ($choice) {
            'all' => 'all',
            'range' => '1-'.$this->faker->numberBetween(1, $totalPages),
            'specific' => implode(',', $this->faker->randomElements(
                range(1, min($totalPages, 20)),
                $this->faker->numberBetween(1, min(5, $totalPages))
            )),
        };
    }

    /**
     * Estado: Pendiente
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'payment_status' => 'pending',
            'paid_at' => null,
            'printed_at' => null,
            'ready_at' => null,
            'delivered_at' => null,
        ]);
    }

    /**
     * Estado: Pagado y en cola
     */
    public function inQueue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_queue',
            'payment_status' => 'paid',
            'paid_at' => now()->subHours(rand(1, 24)),
            'printed_at' => null,
            'ready_at' => null,
            'delivered_at' => null,
        ]);
    }

    /**
     * Estado: Listo para entrega
     */
    public function ready(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ready',
            'payment_status' => 'paid',
            'paid_at' => now()->subDays(2),
            'printed_at' => now()->subDays(1),
            'ready_at' => now()->subHours(3),
            'delivered_at' => null,
        ]);
    }

    /**
     * Estado: Entregado
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'payment_status' => 'paid',
            'paid_at' => now()->subDays(5),
            'printed_at' => now()->subDays(4),
            'ready_at' => now()->subDays(3),
            'delivered_at' => now()->subDays(1),
        ]);
    }

    /**
     * Impresión a color
     */
    public function color(): static
    {
        return $this->state(fn (array $attributes) => [
            'print_type' => 'color',
            'price_per_page' => $attributes['paper_type'] === 'photo_glossy' ? 0.75 : 0.35,
        ]);
    }

    /**
     * Impresión blanco y negro
     */
    public function blackAndWhite(): static
    {
        return $this->state(fn (array $attributes) => [
            'print_type' => 'bw',
            'price_per_page' => $attributes['paper_type'] === 'photo_glossy' ? 0.35 : 0.10,
        ]);
    }

    /**
     * Con engargolado
     */
    public function withBinding(): static
    {
        return $this->state(fn (array $attributes) => [
            'binding' => true,
            'binding_price' => 3.50,
        ]);
    }

    /**
     * Con entrega a domicilio
     */
    public function withDelivery(): static
    {
        return $this->state(fn (array $attributes) => [
            'delivery_method' => 'delivery',
            'branch_id' => null,
            'delivery_address' => $this->faker->address(),
            'delivery_phone' => $this->faker->phoneNumber(),
            'delivery_cost' => $this->faker->randomFloat(2, 2.50, 8.00),
        ]);
    }
}
