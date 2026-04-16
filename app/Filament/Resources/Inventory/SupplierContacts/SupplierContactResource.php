<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\SupplierContacts;

use App\Filament\Resources\Inventory\Concerns\BelongsToInventoryModule;
use App\Filament\Resources\Inventory\SupplierContacts\Pages\CreateSupplierContact;
use App\Filament\Resources\Inventory\SupplierContacts\Pages\EditSupplierContact;
use App\Filament\Resources\Inventory\SupplierContacts\Pages\ListSupplierContacts;
use App\Filament\Resources\Inventory\SupplierContacts\Pages\ViewSupplierContact;
use App\Filament\Resources\Inventory\SupplierContacts\Schemas\SupplierContactForm;
use App\Filament\Resources\Inventory\SupplierContacts\Schemas\SupplierContactInfolist;
use App\Filament\Resources\Inventory\SupplierContacts\Tables\SupplierContactsTable;
use App\Models\Inventory\SupplierContact;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SupplierContactResource extends Resource
{
    use BelongsToInventoryModule;

    protected static ?string $model = SupplierContact::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Contactos proveedor';

    protected static ?string $modelLabel = 'Contacto proveedor';

    protected static ?string $pluralModelLabel = 'Contactos proveedor';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario · Catálogos';

    protected static ?int $navigationSort = 140;

    public static function form(Schema $schema): Schema
    {
        return SupplierContactForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SupplierContactInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SupplierContactsTable::configure($table);
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
            'index' => ListSupplierContacts::route('/'),
            'create' => CreateSupplierContact::route('/create'),
            'view' => ViewSupplierContact::route('/{record}'),
            'edit' => EditSupplierContact::route('/{record}/edit'),
        ];
    }
}
