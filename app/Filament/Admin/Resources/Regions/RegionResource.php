<?php

namespace App\Filament\Admin\Resources\Regions;

use App\Filament\Admin\Resources\Regions\Pages\CreateRegion;
use App\Filament\Admin\Resources\Regions\Pages\EditRegion;
use App\Filament\Admin\Resources\Regions\Pages\ListRegions;
use App\Filament\Admin\Resources\Regions\Schemas\RegionForm;
use App\Filament\Admin\Resources\Regions\Tables\RegionsTable;
use App\Models\Core\Region;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class RegionResource extends Resource
{
    protected static ?string $model = Region::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::GlobeAmericas;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|UnitEnum|null $navigationGroup = null;

    public static function getNavigationLabel(): string
    {
        return __('region.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('region.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('region.plural_model_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('common.navigation_groups.configuracion');
    }

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return RegionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RegionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CountriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRegions::route('/'),
            'create' => CreateRegion::route('/create'),
            'edit' => EditRegion::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
