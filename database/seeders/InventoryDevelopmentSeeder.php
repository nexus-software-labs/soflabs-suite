<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Inventory\Adjustment;
use App\Models\Inventory\Brand;
use App\Models\Inventory\Family;
use App\Models\Inventory\Group;
use App\Models\Inventory\IntakeDocument;
use App\Models\Inventory\IntakeDocumentLine;
use App\Models\Inventory\Movement;
use App\Models\Inventory\OutboundRequest;
use App\Models\Inventory\OutboundRequestLine;
use App\Models\Inventory\Product;
use App\Models\Inventory\Section;
use App\Models\Inventory\Stock;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\SupplierContact;
use App\Models\Inventory\SupplierProduct;
use App\Models\Inventory\Unit;
use App\Models\Inventory\UnitConversion;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseZone;
use App\Models\Tenant;
use App\Services\Inventory\IntakeDocumentWorkflowService;
use App\Services\Inventory\OutboundWorkflowService;
use App\Services\Inventory\StockMovementService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class InventoryDevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        $tenantIds = Tenant::query()
            ->whereHas('modules', fn ($query) => $query->where('module', 'inventory')->where('is_active', true))
            ->pluck('id');

        foreach ($tenantIds as $tenantId) {
            $this->seedTenantInventory((string) $tenantId);
        }
    }

    private function seedTenantInventory(string $tenantId): void
    {
        $this->cleanupTenantInventory($tenantId);

        $warehouses = $this->seedWarehouses($tenantId);
        $units = $this->seedUnits($tenantId);
        $groups = $this->seedHierarchy($tenantId);
        $brands = $this->seedBrands($tenantId);
        $products = $this->seedProducts($tenantId, $groups, $brands, $units);
        $suppliers = $this->seedSuppliers($tenantId);

        $this->seedSupplierRelations($tenantId, $suppliers, $products, $units);
        $this->seedStocks($tenantId, $products, $warehouses);
        $this->seedIntakeDocuments($tenantId, $warehouses, $suppliers, $products);
        $this->seedOutboundRequests($tenantId, $warehouses, $products);
        $this->seedAdjustments($tenantId, $warehouses, $products);
    }

    private function cleanupTenantInventory(string $tenantId): void
    {
        Adjustment::query()->where('tenant_id', $tenantId)->delete();
        Movement::query()->where('tenant_id', $tenantId)->delete();
        OutboundRequestLine::query()->where('tenant_id', $tenantId)->delete();
        OutboundRequest::query()->where('tenant_id', $tenantId)->delete();
        IntakeDocumentLine::query()->where('tenant_id', $tenantId)->delete();
        IntakeDocument::query()->where('tenant_id', $tenantId)->delete();
        Stock::query()->where('tenant_id', $tenantId)->delete();
        SupplierContact::query()->where('tenant_id', $tenantId)->delete();
        SupplierProduct::query()->where('tenant_id', $tenantId)->delete();
        Supplier::query()->where('tenant_id', $tenantId)->delete();
        Product::query()->where('tenant_id', $tenantId)->delete();
        Group::query()->where('tenant_id', $tenantId)->delete();
        Family::query()->where('tenant_id', $tenantId)->delete();
        Section::query()->where('tenant_id', $tenantId)->delete();
        Brand::query()->where('tenant_id', $tenantId)->delete();
        UnitConversion::query()->where('tenant_id', $tenantId)->delete();
        Unit::query()->where('tenant_id', $tenantId)->delete();
        WarehouseZone::query()->where('tenant_id', $tenantId)->delete();
        Warehouse::query()->where('tenant_id', $tenantId)->delete();
    }

    /**
     * @return Collection<int, Warehouse>
     */
    private function seedWarehouses(string $tenantId): Collection
    {
        $warehouses = collect([
            ['name' => 'Main Warehouse', 'warehouse_type' => 'main'],
            ['name' => 'Transit Warehouse', 'warehouse_type' => 'transit'],
        ])->map(fn (array $warehouse) => Warehouse::query()->create([
            'tenant_id' => $tenantId,
            'name' => $warehouse['name'],
            'warehouse_type' => $warehouse['warehouse_type'],
            'location' => fake()->city(),
            'status' => 'active',
        ]));

        foreach ($warehouses as $warehouse) {
            WarehouseZone::query()->create([
                'tenant_id' => $tenantId,
                'warehouse_id' => $warehouse->id,
                'name' => 'Ambient',
                'storage_condition' => 'ambient',
                'status' => 'active',
            ]);
            WarehouseZone::query()->create([
                'tenant_id' => $tenantId,
                'warehouse_id' => $warehouse->id,
                'name' => 'Cold',
                'storage_condition' => 'refrigerated',
                'status' => 'active',
            ]);
        }

        return $warehouses;
    }

    /**
     * @return Collection<string, Unit>
     */
    private function seedUnits(string $tenantId): Collection
    {
        return collect([
            ['name' => 'Unit', 'abbreviation' => 'UNT', 'unit_type' => 'quantity'],
            ['name' => 'Box', 'abbreviation' => 'BOX', 'unit_type' => 'quantity'],
            ['name' => 'Kilogram', 'abbreviation' => 'KG', 'unit_type' => 'weight'],
            ['name' => 'Liter', 'abbreviation' => 'LTR', 'unit_type' => 'volume'],
        ])->mapWithKeys(function (array $unit) use ($tenantId) {
            $record = Unit::query()->create([
                'tenant_id' => $tenantId,
                'name' => $unit['name'],
                'abbreviation' => $unit['abbreviation'],
                'unit_type' => $unit['unit_type'],
                'is_system' => true,
                'status' => 'active',
            ]);

            return [$unit['abbreviation'] => $record];
        });
    }

    /**
     * @return Collection<int, Group>
     */
    private function seedHierarchy(string $tenantId): Collection
    {
        $sections = collect(['Food', 'Hardware', 'Cleaning'])->map(fn (string $name) => Section::query()->create([
            'tenant_id' => $tenantId,
            'name' => $name,
            'description' => fake()->sentence(),
            'status' => 'active',
        ]));

        $groups = collect();
        foreach ($sections as $section) {
            $family = Family::query()->create([
                'tenant_id' => $tenantId,
                'section_id' => $section->id,
                'name' => $section->name.' Family',
                'description' => fake()->sentence(),
                'status' => 'active',
            ]);

            $groups->push(
                Group::query()->create([
                    'tenant_id' => $tenantId,
                    'family_id' => $family->id,
                    'name' => $section->name.' Group A',
                    'description' => fake()->sentence(),
                    'status' => 'active',
                ]),
                Group::query()->create([
                    'tenant_id' => $tenantId,
                    'family_id' => $family->id,
                    'name' => $section->name.' Group B',
                    'description' => fake()->sentence(),
                    'status' => 'active',
                ]),
            );
        }

        return $groups;
    }

    /**
     * @return Collection<int, Brand>
     */
    private function seedBrands(string $tenantId): Collection
    {
        return collect(range(1, 6))->map(fn (int $index) => Brand::query()->create([
            'tenant_id' => $tenantId,
            'name' => 'Brand '.$index,
            'description' => fake()->sentence(),
            'status' => 'active',
        ]));
    }

    /**
     * @param  Collection<int, Group>  $groups
     * @param  Collection<int, Brand>  $brands
     * @param  Collection<string, Unit>  $units
     * @return Collection<int, Product>
     */
    private function seedProducts(string $tenantId, Collection $groups, Collection $brands, Collection $units): Collection
    {
        $products = collect();
        foreach (range(1, 24) as $index) {
            $group = $groups->random();
            $brand = $brands->random();

            $products->push(Product::query()->create([
                'tenant_id' => $tenantId,
                'group_id' => $group->id,
                'brand_id' => $brand->id,
                'sku' => 'SKU-'.Str::upper(Str::random(6)).'-'.$index,
                'name' => fake()->words(3, true),
                'purchase_unit_id' => $units['BOX']->id,
                'stock_unit_id' => $units['UNT']->id,
                'sales_unit_id' => $units['UNT']->id,
                'valuation_method' => fake()->randomElement(['fifo', 'lifo', 'weighted_average']),
                'minimum_stock' => fake()->randomFloat(2, 2, 25),
                'status' => 'active',
            ]));
        }

        UnitConversion::query()->create([
            'tenant_id' => $tenantId,
            'from_unit_id' => $units['BOX']->id,
            'to_unit_id' => $units['UNT']->id,
            'factor' => 12,
        ]);

        return $products;
    }

    /**
     * @return Collection<int, Supplier>
     */
    private function seedSuppliers(string $tenantId): Collection
    {
        return collect(range(1, 8))->map(function (int $index) use ($tenantId) {
            $supplier = Supplier::query()->create([
                'tenant_id' => $tenantId,
                'name' => fake()->company().' '.$index,
                'tax_id' => 'TAX-'.str_pad((string) $index, 4, '0', STR_PAD_LEFT),
                'supplier_type' => fake()->randomElement(['manufacturer', 'distributor', 'importer']),
                'country_code' => fake()->countryCode(),
                'payment_terms' => fake()->randomElement(['cash', 'net_30', 'net_60']),
                'status' => 'active',
            ]);

            SupplierContact::query()->create([
                'tenant_id' => $tenantId,
                'supplier_id' => $supplier->id,
                'name' => fake()->name(),
                'job_title' => fake()->jobTitle(),
                'email' => fake()->safeEmail(),
                'phone' => fake()->phoneNumber(),
                'contact_type' => 'sales',
                'is_primary' => true,
                'status' => 'active',
            ]);

            return $supplier;
        });
    }

    /**
     * @param  Collection<int, Supplier>  $suppliers
     * @param  Collection<int, Product>  $products
     * @param  Collection<string, Unit>  $units
     */
    private function seedSupplierRelations(string $tenantId, Collection $suppliers, Collection $products, Collection $units): void
    {
        foreach ($suppliers as $supplier) {
            foreach ($products->random(6) as $product) {
                SupplierProduct::query()->create([
                    'tenant_id' => $tenantId,
                    'supplier_id' => $supplier->id,
                    'product_id' => $product->id,
                    'price' => fake()->randomFloat(2, 2, 300),
                    'unit_id' => $units['BOX']->id,
                    'supplier_sku' => 'SUP-'.Str::upper(Str::random(6)),
                    'status' => 'active',
                ]);
            }
        }
    }

    /**
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, Warehouse>  $warehouses
     */
    private function seedStocks(string $tenantId, Collection $products, Collection $warehouses): void
    {
        foreach ($products as $product) {
            foreach ($warehouses as $warehouse) {
                Stock::query()->create([
                    'tenant_id' => $tenantId,
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => fake()->randomFloat(2, 20, 220),
                    'reserved_quantity' => 0,
                ]);
            }
        }
    }

    /**
     * @param  Collection<int, Warehouse>  $warehouses
     * @param  Collection<int, Supplier>  $suppliers
     * @param  Collection<int, Product>  $products
     */
    private function seedIntakeDocuments(string $tenantId, Collection $warehouses, Collection $suppliers, Collection $products): void
    {
        $workflow = app(IntakeDocumentWorkflowService::class);

        foreach (range(1, 5) as $index) {
            $document = $workflow->createManualDocument(
                tenantId: $tenantId,
                warehouseId: $warehouses->random()->id,
                createdBy: null,
                supplierId: $suppliers->random()->id,
                documentNumber: 'IN-'.str_pad((string) $index, 4, '0', STR_PAD_LEFT),
                lines: $products->random(3)->values()->map(fn (Product $product) => [
                    'product_id' => $product->id,
                    'description_original' => $product->name,
                    'quantity' => fake()->randomFloat(2, 1, 10),
                    'subtotal' => fake()->randomFloat(2, 15, 200),
                ])->all(),
            );

            $workflow->approve($document);
        }
    }

    /**
     * @param  Collection<int, Warehouse>  $warehouses
     * @param  Collection<int, Product>  $products
     */
    private function seedOutboundRequests(string $tenantId, Collection $warehouses, Collection $products): void
    {
        $outboundWorkflow = app(OutboundWorkflowService::class);

        foreach (range(1, 4) as $index) {
            $warehouse = $warehouses->random();
            $request = $outboundWorkflow->createRequest(
                tenantId: $tenantId,
                warehouseId: $warehouse->id,
                createdBy: null,
                requestNumber: 'OUT-'.str_pad((string) $index, 4, '0', STR_PAD_LEFT),
                lines: $products->random(2)->values()->map(fn (Product $product) => [
                    'product_id' => $product->id,
                    'requested_quantity' => fake()->randomFloat(2, 1, 5),
                ])->all(),
            );

            $reserved = $outboundWorkflow->reserve($request);
            $outboundWorkflow->dispatch($reserved);
        }
    }

    /**
     * @param  Collection<int, Warehouse>  $warehouses
     * @param  Collection<int, Product>  $products
     */
    private function seedAdjustments(string $tenantId, Collection $warehouses, Collection $products): void
    {
        $stockMovementService = app(StockMovementService::class);

        foreach (range(1, 6) as $_) {
            $product = $products->random();
            $warehouse = $warehouses->random();
            $difference = fake()->boolean()
                ? fake()->randomFloat(2, 1, 3)
                : fake()->randomFloat(2, -3, -1);

            $stockMovementService->registerAdjustment(
                tenantId: $tenantId,
                productId: $product->id,
                warehouseId: $warehouse->id,
                differenceQuantity: $difference,
                reason: fake()->randomElement(['count_error', 'damage', 'loss', 'donation']),
                notes: fake()->sentence(),
            );
        }
    }
}
