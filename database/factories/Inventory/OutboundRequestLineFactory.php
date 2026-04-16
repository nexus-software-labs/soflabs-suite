<?php

declare(strict_types=1);

namespace Database\Factories\Inventory;

use App\Models\Inventory\OutboundRequest;
use App\Models\Inventory\OutboundRequestLine;
use App\Models\Inventory\Product;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OutboundRequestLine>
 */
class OutboundRequestLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'outbound_request_id' => OutboundRequest::factory(),
            'line_number' => fake()->numberBetween(1, 10),
            'product_id' => Product::factory(),
            'requested_quantity' => fake()->randomFloat(2, 1, 100),
            'reserved_quantity' => 0,
            'dispatched_quantity' => 0,
            'status' => 'requested',
        ];
    }
}
