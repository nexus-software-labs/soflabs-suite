<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Filament;

use App\Filament\Resources\Inventory\Adjustments\AdjustmentResource;
use App\Filament\Resources\Inventory\Brands\BrandResource;
use App\Filament\Resources\Inventory\Families\FamilyResource;
use App\Filament\Resources\Inventory\Groups\GroupResource;
use App\Filament\Resources\Inventory\IntakeDocuments\IntakeDocumentResource;
use App\Filament\Resources\Inventory\Movements\MovementResource;
use App\Filament\Resources\Inventory\OutboundRequests\OutboundRequestResource;
use App\Filament\Resources\Inventory\Products\ProductResource;
use App\Filament\Resources\Inventory\Sections\SectionResource;
use App\Filament\Resources\Inventory\Stocks\StockResource;
use App\Filament\Resources\Inventory\SupplierContacts\SupplierContactResource;
use App\Filament\Resources\Inventory\SupplierProducts\SupplierProductResource;
use App\Filament\Resources\Inventory\Suppliers\SupplierResource;
use App\Filament\Resources\Inventory\UnitConversions\UnitConversionResource;
use App\Filament\Resources\Inventory\Units\UnitResource;
use App\Filament\Resources\Inventory\Warehouses\WarehouseResource;
use App\Filament\Resources\Inventory\WarehouseZones\WarehouseZoneResource;
use App\Filament\Resources\Inventory\Widgets\InventoryAlertsWidget;
use App\Filament\Resources\Inventory\Widgets\InventoryKpiOverviewWidget;
use App\Filament\Resources\Inventory\Widgets\InventoryMovementsTrendWidget;
use Filament\Contracts\Plugin;
use Filament\Panel;

final class InventoryPlugin implements Plugin
{
    /**
     * Clases de recurso del módulo inventario. Deben figurar en
     * el panel de aplicación para que Filament registre rutas al cargar
     * `routes/web.php`; no basta con registrar solo en
     * `TenantModulePluginsRegistration` vía
     * `bootUsing`, que corre después del registro de rutas.
     *
     * @return list<class-string>
     */
    public static function resourceClasses(): array
    {
        return [
            IntakeDocumentResource::class,
            OutboundRequestResource::class,
            AdjustmentResource::class,
            StockResource::class,
            MovementResource::class,
            ProductResource::class,
            SupplierResource::class,
            WarehouseResource::class,
            UnitResource::class,
            BrandResource::class,
            SectionResource::class,
            FamilyResource::class,
            GroupResource::class,
            SupplierContactResource::class,
            SupplierProductResource::class,
            WarehouseZoneResource::class,
            UnitConversionResource::class,
        ];
    }

    public function getId(): string
    {
        return 'inventory';
    }

    public function register(Panel $panel): void
    {
        $panel->widgets([
            InventoryKpiOverviewWidget::class,
            InventoryMovementsTrendWidget::class,
            InventoryAlertsWidget::class,
        ]);
    }

    public function boot(Panel $panel): void {}
}
