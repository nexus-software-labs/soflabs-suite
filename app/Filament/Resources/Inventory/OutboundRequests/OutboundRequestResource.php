<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\OutboundRequests;

use App\Filament\Resources\Inventory\Concerns\BelongsToInventoryModule;
use App\Filament\Resources\Inventory\OutboundRequests\Pages\CreateOutboundRequest;
use App\Filament\Resources\Inventory\OutboundRequests\Pages\EditOutboundRequest;
use App\Filament\Resources\Inventory\OutboundRequests\Pages\ListOutboundRequests;
use App\Filament\Resources\Inventory\OutboundRequests\Pages\ViewOutboundRequest;
use App\Filament\Resources\Inventory\OutboundRequests\Schemas\OutboundRequestForm;
use App\Filament\Resources\Inventory\OutboundRequests\Schemas\OutboundRequestInfolist;
use App\Filament\Resources\Inventory\OutboundRequests\Tables\OutboundRequestsTable;
use App\Models\Inventory\OutboundRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OutboundRequestResource extends Resource
{
    use BelongsToInventoryModule;

    protected static ?string $model = OutboundRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Solicitudes de salida';

    protected static ?string $modelLabel = 'Solicitud de salida';

    protected static ?string $pluralModelLabel = 'Solicitudes de salida';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario · Operaciones';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return OutboundRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OutboundRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OutboundRequestsTable::configure($table);
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
            'index' => ListOutboundRequests::route('/'),
            'create' => CreateOutboundRequest::route('/create'),
            'view' => ViewOutboundRequest::route('/{record}'),
            'edit' => EditOutboundRequest::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('inventory.outbound.request') ?? false;
    }

    public static function canEdit($record): bool
    {
        return $record->status === 'requested';
    }
}
