<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Adjustments;

use App\Filament\Resources\Inventory\Adjustments\Pages\CreateAdjustment;
use App\Filament\Resources\Inventory\Adjustments\Pages\EditAdjustment;
use App\Filament\Resources\Inventory\Adjustments\Pages\ListAdjustments;
use App\Filament\Resources\Inventory\Adjustments\Pages\ViewAdjustment;
use App\Filament\Resources\Inventory\Adjustments\Schemas\AdjustmentForm;
use App\Filament\Resources\Inventory\Adjustments\Schemas\AdjustmentInfolist;
use App\Filament\Resources\Inventory\Adjustments\Tables\AdjustmentsTable;
use App\Filament\Resources\Inventory\Concerns\BelongsToInventoryModule;
use App\Models\Inventory\Adjustment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AdjustmentResource extends Resource
{
    use BelongsToInventoryModule;

    protected static ?string $model = Adjustment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Ajustes';

    protected static ?string $modelLabel = 'Ajuste';

    protected static ?string $pluralModelLabel = 'Ajustes';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario · Operaciones';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return AdjustmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AdjustmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdjustmentsTable::configure($table);
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
            'index' => ListAdjustments::route('/'),
            'create' => CreateAdjustment::route('/create'),
            'view' => ViewAdjustment::route('/{record}'),
            'edit' => EditAdjustment::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('inventory.adjustments.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }
}
