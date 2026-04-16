<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\IntakeDocuments;

use App\Filament\Resources\Inventory\Concerns\BelongsToInventoryModule;
use App\Filament\Resources\Inventory\IntakeDocuments\Pages\CreateIntakeDocument;
use App\Filament\Resources\Inventory\IntakeDocuments\Pages\EditIntakeDocument;
use App\Filament\Resources\Inventory\IntakeDocuments\Pages\ListIntakeDocuments;
use App\Filament\Resources\Inventory\IntakeDocuments\Pages\ViewIntakeDocument;
use App\Filament\Resources\Inventory\IntakeDocuments\Schemas\IntakeDocumentForm;
use App\Filament\Resources\Inventory\IntakeDocuments\Schemas\IntakeDocumentInfolist;
use App\Filament\Resources\Inventory\IntakeDocuments\Tables\IntakeDocumentsTable;
use App\Models\Inventory\IntakeDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IntakeDocumentResource extends Resource
{
    use BelongsToInventoryModule;

    protected static ?string $model = IntakeDocument::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Documentos de entrada';

    protected static ?string $modelLabel = 'Documento de entrada';

    protected static ?string $pluralModelLabel = 'Documentos de entrada';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario · Operaciones';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return IntakeDocumentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return IntakeDocumentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IntakeDocumentsTable::configure($table);
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
            'index' => ListIntakeDocuments::route('/'),
            'create' => CreateIntakeDocument::route('/create'),
            'view' => ViewIntakeDocument::route('/{record}'),
            'edit' => EditIntakeDocument::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('inventory.intake.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return in_array($record->status, ['review', 'received', 'processing'], true);
    }
}
