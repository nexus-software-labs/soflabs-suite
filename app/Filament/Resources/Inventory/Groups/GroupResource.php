<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Groups;

use App\Filament\Resources\Inventory\Concerns\BelongsToInventoryModule;
use App\Filament\Resources\Inventory\Groups\Pages\CreateGroup;
use App\Filament\Resources\Inventory\Groups\Pages\EditGroup;
use App\Filament\Resources\Inventory\Groups\Pages\ListGroups;
use App\Filament\Resources\Inventory\Groups\Pages\ViewGroup;
use App\Filament\Resources\Inventory\Groups\Schemas\GroupForm;
use App\Filament\Resources\Inventory\Groups\Schemas\GroupInfolist;
use App\Filament\Resources\Inventory\Groups\Tables\GroupsTable;
use App\Models\Inventory\Group;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GroupResource extends Resource
{
    use BelongsToInventoryModule;

    protected static ?string $model = Group::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Grupos';

    protected static ?string $modelLabel = 'Grupo';

    protected static ?string $pluralModelLabel = 'Grupos';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario · Catálogos';

    protected static ?int $navigationSort = 130;

    public static function form(Schema $schema): Schema
    {
        return GroupForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GroupInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GroupsTable::configure($table);
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
            'index' => ListGroups::route('/'),
            'create' => CreateGroup::route('/create'),
            'view' => ViewGroup::route('/{record}'),
            'edit' => EditGroup::route('/{record}/edit'),
        ];
    }
}
