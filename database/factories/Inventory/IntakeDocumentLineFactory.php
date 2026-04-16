<?php

declare(strict_types=1);

namespace Database\Factories\Inventory;

use App\Models\Inventory\IntakeDocument;
use App\Models\Inventory\IntakeDocumentLine;
use App\Models\Inventory\Product;
use App\Models\Inventory\Unit;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IntakeDocumentLine>
 */
class IntakeDocumentLineFactory extends Factory
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
            'intake_document_id' => IntakeDocument::factory(),
            'line_number' => fake()->numberBetween(1, 10),
            'product_id' => Product::factory(),
            'description_original' => fake()->words(4, true),
            'quantity' => fake()->randomFloat(2, 1, 100),
            'unit_id' => Unit::factory(),
            'unit_price' => fake()->optional()->randomFloat(2, 1, 500),
            'subtotal' => fake()->optional()->randomFloat(2, 1, 5000),
            'linked_manually' => false,
            'status' => 'pending_review',
        ];
    }
}
