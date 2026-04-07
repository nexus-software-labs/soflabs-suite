<?php

namespace App\Filament\Admin\Resources\PrintOrders;

use App\Filament\Admin\Resources\PrintOrders\Pages\CreatePrintOrder;
use App\Filament\Admin\Resources\PrintOrders\Pages\EditPrintOrder;
use App\Filament\Admin\Resources\PrintOrders\Pages\ListPrintOrders;
use App\Filament\Admin\Resources\PrintOrders\Schemas\PrintOrderForm;
use App\Filament\Admin\Resources\PrintOrders\Tables\PrintOrdersTable;
use App\Models\Printing\PrintOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PrintOrderResource extends Resource
{
    protected static ?string $model = PrintOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Printer;

    protected static ?string $recordTitleAttribute = 'order_number';

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|UnitEnum|null $navigationGroup = null;

    public static function getNavigationLabel(): string
    {
        return __('print_order.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('print_order.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('print_order.plural_model_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('common.navigation_groups.gestion_pedidos');
    }

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PrintOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrintOrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrintOrders::route('/'),
            // 'create' => CreatePrintOrder::route('/create'),
            'edit' => EditPrintOrder::route('/{record}/edit'),
        ];
    }

    // public static function getNavigationBadge(): ?string
    // {
    //     return static::getModel()::where('status', 'pending')->count();
    // }

    // public static function getNavigationBadgeColor(): string|array|null
    // {
    //     return 'warning';
    // }
}
