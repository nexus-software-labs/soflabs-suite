<?php

namespace App\Filament\Admin\Resources\CustomerTiers;

use App\Filament\Admin\Resources\CustomerTiers\Pages\CreateCustomerTier;
use App\Filament\Admin\Resources\CustomerTiers\Pages\EditCustomerTier;
use App\Filament\Admin\Resources\CustomerTiers\Pages\ListCustomerTiers;
use App\Filament\Admin\Resources\CustomerTiers\Schemas\CustomerTierForm;
use App\Filament\Admin\Resources\CustomerTiers\Tables\CustomerTiersTable;
use App\Models\Core\CustomerTier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CustomerTierResource extends Resource
{
    protected static ?string $model = CustomerTier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Star;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|UnitEnum|null $navigationGroup = null;

    public static function getNavigationLabel(): string
    {
        return __('customer_tier.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('customer_tier.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('customer_tier.plural_model_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('common.navigation_groups.configuracion');
    }

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return CustomerTierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerTiersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BenefitsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerTiers::route('/'),
            'create' => CreateCustomerTier::route('/create'),
            'edit' => EditCustomerTier::route('/{record}/edit'),
        ];
    }
}
