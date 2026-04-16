<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Sections;

use App\Filament\Resources\Inventory\Concerns\BelongsToInventoryModule;
use App\Filament\Resources\Inventory\Sections\Pages\CreateSection;
use App\Filament\Resources\Inventory\Sections\Pages\EditSection;
use App\Filament\Resources\Inventory\Sections\Pages\ListSections;
use App\Filament\Resources\Inventory\Sections\Pages\ViewSection;
use App\Filament\Resources\Inventory\Sections\Schemas\SectionForm;
use App\Filament\Resources\Inventory\Sections\Schemas\SectionInfolist;
use App\Filament\Resources\Inventory\Sections\Tables\SectionsTable;
use App\Models\Inventory\Section;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SectionResource extends Resource
{
    use BelongsToInventoryModule;

    protected static ?string $model = Section::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Secciones';

    protected static ?string $modelLabel = 'Sección';

    protected static ?string $pluralModelLabel = 'Secciones';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario · Catálogos';

    protected static ?int $navigationSort = 110;

    public static function form(Schema $schema): Schema
    {
        return SectionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SectionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SectionsTable::configure($table);
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
            'index' => ListSections::route('/'),
            'create' => CreateSection::route('/create'),
            'view' => ViewSection::route('/{record}'),
            'edit' => EditSection::route('/{record}/edit'),
        ];
    }
}
