<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\UnitConversions;

use App\Filament\Resources\Inventory\Concerns\BelongsToInventoryModule;
use App\Filament\Resources\Inventory\UnitConversions\Pages\CreateUnitConversion;
use App\Filament\Resources\Inventory\UnitConversions\Pages\EditUnitConversion;
use App\Filament\Resources\Inventory\UnitConversions\Pages\ListUnitConversions;
use App\Filament\Resources\Inventory\UnitConversions\Pages\ViewUnitConversion;
use App\Filament\Resources\Inventory\UnitConversions\Schemas\UnitConversionForm;
use App\Filament\Resources\Inventory\UnitConversions\Schemas\UnitConversionInfolist;
use App\Filament\Resources\Inventory\UnitConversions\Tables\UnitConversionsTable;
use App\Models\Inventory\UnitConversion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UnitConversionResource extends Resource
{
    use BelongsToInventoryModule;

    protected static ?string $model = UnitConversion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Conversiones de unidad';

    protected static ?string $modelLabel = 'Conversión de unidad';

    protected static ?string $pluralModelLabel = 'Conversiones de unidad';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario · Catálogos';

    protected static ?int $navigationSort = 170;

    public static function form(Schema $schema): Schema
    {
        return UnitConversionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UnitConversionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UnitConversionsTable::configure($table);
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
            'index' => ListUnitConversions::route('/'),
            'create' => CreateUnitConversion::route('/create'),
            'view' => ViewUnitConversion::route('/{record}'),
            'edit' => EditUnitConversion::route('/{record}/edit'),
        ];
    }
}
