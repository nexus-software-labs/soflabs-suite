<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Movements;

use App\Filament\Resources\Inventory\Concerns\BelongsToInventoryModule;
use App\Filament\Resources\Inventory\Movements\Pages\CreateMovement;
use App\Filament\Resources\Inventory\Movements\Pages\EditMovement;
use App\Filament\Resources\Inventory\Movements\Pages\ListMovements;
use App\Filament\Resources\Inventory\Movements\Pages\ViewMovement;
use App\Filament\Resources\Inventory\Movements\Schemas\MovementForm;
use App\Filament\Resources\Inventory\Movements\Schemas\MovementInfolist;
use App\Filament\Resources\Inventory\Movements\Tables\MovementsTable;
use App\Models\Inventory\Movement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MovementResource extends Resource
{
    use BelongsToInventoryModule;

    protected static ?string $model = Movement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Kardex';

    protected static ?string $modelLabel = 'Movimiento';

    protected static ?string $pluralModelLabel = 'Movimientos';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario · Stock y trazabilidad';

    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return MovementForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MovementInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MovementsTable::configure($table);
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
            'index' => ListMovements::route('/'),
            'create' => CreateMovement::route('/create'),
            'view' => ViewMovement::route('/{record}'),
            'edit' => EditMovement::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
