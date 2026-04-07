<?php

namespace App\Filament\Admin\Resources\Promotions\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('promotion.sections.promotion_info'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('promotion.fields.name'))
                            ->required()
                            ->maxLength(255)
                            ->placeholder(__('promotion.placeholders.name'))
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label(__('promotion.fields.description'))
                            ->required()
                            ->rows(3)
                            ->placeholder(__('promotion.placeholders.description'))
                            ->helperText(__('promotion.helpers.description'))
                            ->columnSpanFull(),
                    ])->columnSpanFull(),

                Section::make(__('promotion.sections.application_type'))
                    ->schema([
                        Select::make('application_type')
                            ->label(__('promotion.fields.application_type'))
                            ->required()
                            ->options(__('promotion.application_types'))
                            ->default('automatic')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) { // 🎯 Agregar $get aquí
                                if ($state === 'coupon' && ! $get('coupon_code')) {
                                    // Generar código automático
                                    $set('coupon_code', strtoupper(Str::random(8)));
                                }
                            })
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('coupon_code')
                                    ->label(__('promotion.fields.coupon_code'))
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50)
                                    ->placeholder(__('promotion.placeholders.coupon_code'))
                                    ->helperText(__('promotion.helpers.coupon_code'))
                                    ->suffixAction(
                                        Action::make('generate')
                                            ->icon('heroicon-o-arrow-path')
                                            ->action(fn (callable $set) => $set('coupon_code', strtoupper(Str::random(8))))
                                    ),

                                TextInput::make('usage_limit')
                                    ->label(__('promotion.fields.usage_limit'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->placeholder(__('promotion.placeholders.usage_limit'))
                                    ->helperText(__('promotion.helpers.usage_limit')),
                            ])
                            ->visible(fn (Get $get) => $get('application_type') === 'coupon'),
                    ])
                    ->columnSpanFull(),

                Section::make(__('promotion.sections.discount_type'))
                    ->schema([
                        Select::make('discount_type')
                            ->label(__('promotion.fields.discount_type'))
                            ->required()
                            ->options(__('promotion.discount_types'))
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state === 'free_delivery') {
                                    $set('applies_to', 'delivery');
                                    $set('discount_value', null);
                                }
                            })
                            ->columnSpanFull(),

                        Select::make('applies_to')
                            ->label(__('promotion.fields.applies_to'))
                            ->required()
                            ->options(__('promotion.applies_to_options'))
                            ->default('delivery')
                            ->disabled(fn (Get $get) => $get('discount_type') === 'free_delivery')
                            ->dehydrated()
                            ->helperText(
                                fn (Get $get) => $get('discount_type') === 'free_delivery'
                                    ? __('promotion.helpers.applies_to_free_delivery')
                                    : null
                            )
                            ->columnSpanFull(),

                        TextInput::make('discount_value')
                            ->label(
                                fn (Get $get) => $get('discount_type') === 'percentage'
                                    ? __('promotion.discount_labels.percentage')
                                    : ($get('discount_type') === 'fixed_rate'
                                        ? __('promotion.discount_types.fixed_rate')
                                        : __('promotion.discount_labels.fixed'))
                            )
                            ->numeric()
                            ->required(fn (Get $get) => $get('discount_type') !== 'free_delivery')
                            ->visible(fn (Get $get) => $get('discount_type') !== 'free_delivery')
                            ->suffix(
                                fn (Get $get) => $get('discount_type') === 'percentage'
                                    ? '%'
                                    : ($get('discount_type') === 'fixed_rate'
                                        ? '$/lb'
                                        : '$')
                            )
                            ->helperText(
                                fn (Get $get) => $get('discount_type') === 'fixed_rate'
                                    ? __('promotion.helpers.fixed_rate_value')
                                    : null
                            )
                            ->minValue(0)
                            ->maxValue(
                                fn (Get $get) => $get('discount_type') === 'percentage' ? 100 : null
                            )
                            ->step(
                                fn (Get $get) => $get('discount_type') === 'percentage' ? 1 : 0.01
                            ),

                        TextInput::make('max_discount_amount')
                            ->label(__('promotion.fields.max_discount_amount'))
                            ->numeric()
                            ->prefix('$')
                            ->visible(fn (Get $get) => $get('discount_type') === 'percentage')
                            ->helperText(__('promotion.helpers.max_discount'))
                            ->minValue(0)
                            ->step(0.01),
                    ]),

                Section::make(__('promotion.sections.scope'))
                    ->description(__('promotion.sections.scope_description'))
                    ->schema([
                        Select::make('scope_type')
                            ->label(__('promotion.fields.scope_type'))
                            ->required()
                            ->options(__('promotion.scope_types'))
                            ->default('all')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Limpiar campos según el tipo de alcance seleccionado
                                if ($state === 'all' || $state === 'branches' || $state === 'customers') {
                                    $set('region_id', null);
                                    $set('country_id', null);
                                }
                                if ($state !== 'branches') {
                                    $set('branches', []);
                                }
                                if ($state !== 'customers') {
                                    $set('customers', []);
                                }
                                if ($state === 'all' || $state === 'region' || $state === 'country' || $state === 'branches') {
                                    $set('customers', []);
                                }
                                // Limpiar categoría si no es necesario
                                if ($state !== 'all') {
                                    $set('customer_tier_id', null);
                                }
                            })
                            ->columnSpanFull(),

                        Select::make('customer_tier_id')
                            ->label(__('promotion.fields.customer_tier_id'))
                            ->relationship('customerTier', 'name', function ($query) {
                                $query->where('is_active', true)->orderBy('priority', 'desc');
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder(__('promotion.placeholders.all_categories'))
                            ->helperText(__('promotion.helpers.customer_tier'))
                            ->visible(fn (Get $get) => $get('scope_type') === 'all')
                            ->columnSpanFull(),

                        Select::make('region_id')
                            ->label(__('promotion.fields.region_id'))
                            ->relationship('region', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => $get('scope_type') === 'region')
                            ->required(fn (Get $get) => $get('scope_type') === 'region')
                            ->helperText(__('promotion.helpers.region'))
                            ->columnSpanFull(),

                        Select::make('country_id')
                            ->label(__('promotion.fields.country_id'))
                            ->relationship('country', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => $get('scope_type') === 'country')
                            ->required(fn (Get $get) => $get('scope_type') === 'country')
                            ->helperText(__('promotion.helpers.country'))
                            ->columnSpanFull(),

                        Select::make('branches')
                            ->label(__('promotion.fields.branches'))
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
                            ->helperText(__('promotion.helpers.branches'))
                            ->columnSpanFull(),

                        Select::make('customers')
                            ->label(__('promotion.fields.customers'))
                            ->relationship('customers', 'id')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(function ($record) {
                                $user = $record->user;
                                if (! $user) {
                                    return "Customer #{$record->id}";
                                }
                                $email = $user->email ?? __('promotion.placeholders.no_email');
                                $name = $user->name ?? __('promotion.placeholders.no_name');
                                $locker = $record->locker_code ? " - Casillero: {$record->locker_code}" : '';

                                return "{$name} ({$email}){$locker}";
                            })
                            ->visible(fn (Get $get) => $get('scope_type') === 'customers')
                            ->required(fn (Get $get) => $get('scope_type') === 'customers')
                            ->helperText(__('promotion.helpers.customers'))
                            ->columnSpanFull(),
                    ]),

                Section::make(__('promotion.sections.config'))
                    ->schema([
                        Select::make('service_type')
                            ->label(__('promotion.fields.service_type'))
                            ->required()
                            ->options(__('promotion.service_types'))
                            ->default('both')
                            ->columnSpanFull(),

                        TextInput::make('min_order_amount')
                            ->label(__('promotion.fields.min_order_amount'))
                            ->numeric()
                            ->prefix('$')
                            ->helperText(__('promotion.helpers.min_order_amount'))
                            ->minValue(0)
                            ->step(0.01),

                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('starts_at')
                                    ->label(__('promotion.fields.starts_at'))
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->seconds(false),

                                DateTimePicker::make('expires_at')
                                    ->label(__('promotion.fields.expires_at'))
                                    ->required()
                                    ->after('starts_at')
                                    ->native(false)
                                    ->seconds(false),
                            ]),

                        Toggle::make('active')
                            ->label(__('promotion.fields.active'))
                            ->default(true)
                            ->helperText(__('promotion.helpers.active'))
                            ->inline(false),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
