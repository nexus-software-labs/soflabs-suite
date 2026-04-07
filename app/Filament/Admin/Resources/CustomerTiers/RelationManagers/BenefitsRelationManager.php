<?php

namespace App\Filament\Admin\Resources\CustomerTiers\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class BenefitsRelationManager extends RelationManager
{
    protected static string $relationship = 'allBenefits';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return (string) __('customer_tier.benefits.title');
    }

    public static function getModelLabel(): string
    {
        return (string) __('customer_tier.benefits.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return (string) __('customer_tier.benefits.plural_model_label');
    }

    public function form(Schema $schema): Schema
    {
        $b = 'customer_tier.benefits';

        return $schema
            ->components([
                Section::make(__("{$b}.sections.info"))
                    ->schema([
                        TextInput::make('name')
                            ->label(__("{$b}.fields.name"))
                            ->required()
                            ->maxLength(255)
                            ->placeholder(__("{$b}.placeholders.name"))
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label(__("{$b}.fields.description"))
                            ->rows(3)
                            ->placeholder(__("{$b}.placeholders.description"))
                            ->helperText(__("{$b}.helpers.description"))
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make(__("{$b}.sections.type"))
                    ->schema([
                        Select::make('discount_type')
                            ->label(__("{$b}.fields.discount_type"))
                            ->required()
                            ->options([
                                'percentage' => __("{$b}.discount_types.percentage"),
                                'fixed_amount' => __("{$b}.discount_types.fixed_amount"),
                                'fixed_rate' => __("{$b}.discount_types.fixed_rate"),
                            ])
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state === 'fixed_rate') {
                                    $set('applies_to', 'weight');
                                }
                            })
                            ->columnSpanFull(),

                        Select::make('applies_to')
                            ->label(__("{$b}.fields.applies_to"))
                            ->required()
                            ->options([
                                'delivery' => __("{$b}.applies_to.delivery"),
                                'subtotal' => __("{$b}.applies_to.subtotal"),
                                'weight' => __("{$b}.applies_to.weight"),
                            ])
                            ->default('delivery')
                            ->reactive()
                            ->helperText(
                                fn (Get $get) => $get('discount_type') === 'fixed_rate'
                                    ? __("{$b}.helpers.fixed_rate_applies")
                                    : null
                            )
                            ->columnSpanFull(),

                        TextInput::make('discount_value')
                            ->label(
                                fn (Get $get) => match ($get('discount_type')) {
                                    'percentage' => __("{$b}.fields.discount_percentage"),
                                    'fixed_rate' => __("{$b}.fields.fixed_rate"),
                                    default => __("{$b}.fields.discount_fixed"),
                                }
                            )
                            ->numeric()
                            ->required()
                            ->suffix(
                                fn (Get $get) => match ($get('discount_type')) {
                                    'percentage' => '%',
                                    'fixed_rate' => '$/lb',
                                    default => '$',
                                }
                            )
                            ->helperText(
                                fn (Get $get) => $get('discount_type') === 'fixed_rate'
                                    ? __("{$b}.helpers.fixed_rate_value")
                                    : null
                            )
                            ->minValue(0)
                            ->maxValue(
                                fn (Get $get) => $get('discount_type') === 'percentage' ? 100 : null
                            )
                            ->step(
                                fn (Get $get) => $get('discount_type') === 'percentage' ? 1 : 0.01
                            )
                            ->columnSpanFull(),

                        TextInput::make('max_discount_amount')
                            ->label(__("{$b}.fields.max_discount_amount"))
                            ->numeric()
                            ->prefix('$')
                            ->visible(fn (Get $get) => $get('discount_type') === 'percentage')
                            ->helperText(__("{$b}.helpers.max_discount"))
                            ->minValue(0)
                            ->step(0.01),
                    ])
                    ->columnSpanFull(),

                Section::make(__("{$b}.sections.scope"))
                    ->description(__("{$b}.sections.scope_description"))
                    ->schema([
                        Select::make('scope_type')
                            ->label(__("{$b}.fields.scope_type"))
                            ->required()
                            ->options([
                                'all' => __("{$b}.scope_types.all"),
                                'region' => __("{$b}.scope_types.region"),
                                'country' => __("{$b}.scope_types.country"),
                                'branches' => __("{$b}.scope_types.branches"),
                            ])
                            ->default('all')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Limpiar campos según el tipo de alcance seleccionado
                                if ($state === 'all' || $state === 'branches') {
                                    $set('region_id', null);
                                    $set('country_id', null);
                                }
                                if ($state !== 'branches') {
                                    $set('branches', []);
                                }
                                if ($state === 'region') {
                                    $set('country_id', null);
                                    $set('branches', []);
                                }
                                if ($state === 'country') {
                                    $set('region_id', null);
                                    $set('branches', []);
                                }
                            })
                            ->columnSpanFull(),

                        Select::make('region_id')
                            ->label(__("{$b}.fields.region"))
                            ->relationship('region', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => $get('scope_type') === 'region')
                            ->required(fn (Get $get) => $get('scope_type') === 'region')
                            ->helperText(__("{$b}.helpers.region"))
                            ->columnSpanFull(),

                        Select::make('country_id')
                            ->label(__("{$b}.fields.country"))
                            ->relationship('country', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => $get('scope_type') === 'country')
                            ->required(fn (Get $get) => $get('scope_type') === 'country')
                            ->helperText(__("{$b}.helpers.country"))
                            ->columnSpanFull(),

                        Select::make('branches')
                            ->label(__("{$b}.fields.branches"))
                            ->relationship(
                                'branches',
                                'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->withoutGlobalScopes()
                            )
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(
                                fn ($record) => $record->name.' ('.($record->countryModel?->name ?? $record->country ?? '—').')'
                            )
                            ->visible(fn (Get $get) => $get('scope_type') === 'branches')
                            ->required(fn (Get $get) => $get('scope_type') === 'branches')
                            ->helperText(__("{$b}.helpers.branches"))
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make(__("{$b}.sections.config"))
                    ->schema([
                        Select::make('service_type')
                            ->label(__("{$b}.fields.service_type"))
                            ->required()
                            ->options([
                                'both' => __("{$b}.service_types.both"),
                                'print_order' => __("{$b}.service_types.print_order"),
                                'pre_alert' => __("{$b}.service_types.pre_alert"),
                            ])
                            ->default('both')
                            ->columnSpanFull(),

                        TextInput::make('min_order_amount')
                            ->label(__("{$b}.fields.min_order_amount"))
                            ->numeric()
                            ->prefix('$')
                            ->helperText(__("{$b}.helpers.min_order"))
                            ->minValue(0)
                            ->step(0.01),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('priority')
                                    ->label(__("{$b}.fields.priority"))
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->helperText(__("{$b}.helpers.priority")),

                                Toggle::make('is_active')
                                    ->label(__("{$b}.fields.is_active"))
                                    ->default(true)
                                    ->helperText(__("{$b}.helpers.is_active"))
                                    ->inline(false),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        $b = 'customer_tier.benefits';

        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__("{$b}.table.name"))
                    ->searchable()
                    ->weight('bold')
                    ->description(fn ($record): string => $record->description ?? ''),

                TextColumn::make('discount_type')
                    ->label(__("{$b}.table.discount_type"))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'percentage' => 'success',
                        'fixed_amount' => 'info',
                        'fixed_rate' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => Arr::get(__("{$b}.table_discount_types"), $state, 'N/A')),

                TextColumn::make('applies_to')
                    ->label(__("{$b}.table.applies_to"))
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn (?string $state): string => Arr::get(__("{$b}.table_applies_to"), $state, 'N/A')),

                TextColumn::make('discount_value')
                    ->label(__("{$b}.table.value"))
                    ->formatStateUsing(function ($record) {
                        if (! $record->discount_value) {
                            return 'N/A';
                        }
                        $suffix = match ($record->discount_type) {
                            'percentage' => '%',
                            'fixed_rate' => '$/lb',
                            default => '$',
                        };

                        return number_format($record->discount_value, 2).$suffix;
                    }),

                TextColumn::make('scope_type')
                    ->label(__("{$b}.table.scope"))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Arr::get(__("{$b}.table_scope"), $state, 'N/A')),

                TextColumn::make('service_type')
                    ->label(__("{$b}.table.services"))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Arr::get(__("{$b}.table_services"), $state, 'N/A')),

                TextColumn::make('priority')
                    ->label(__("{$b}.table.priority"))
                    ->sortable()
                    ->badge()
                    ->color(
                        fn (int $state): string => match (true) {
                            $state >= 75 => 'success',
                            $state >= 50 => 'warning',
                            default => 'gray',
                        }
                    ),

                IconColumn::make('is_active')
                    ->label(__("{$b}.table.state"))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__("{$b}.table.created_at"))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__("{$b}.filters.state"))
                    ->placeholder(__("{$b}.filters.all"))
                    ->trueLabel(__("{$b}.filters.active"))
                    ->falseLabel(__("{$b}.filters.inactive")),

                SelectFilter::make('discount_type')
                    ->label(__("{$b}.filters.discount_type"))
                    ->options(fn () => __("{$b}.table_discount_types")),

                SelectFilter::make('scope_type')
                    ->label(__("{$b}.filters.scope"))
                    ->options(fn () => __("{$b}.filter_scope")),

                SelectFilter::make('service_type')
                    ->label(__("{$b}.filters.services"))
                    ->options(fn () => __("{$b}.table_services")),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('priority', 'desc');
    }
}
