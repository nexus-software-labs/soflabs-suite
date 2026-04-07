<?php

namespace App\Filament\Admin\Resources\Countries\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CountryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('country.sections.general_info'))
                    ->schema([
                        Select::make('region_id')
                            ->label(__('country.fields.region'))
                            ->relationship('region', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label(__('country.fields.region_name'))
                                    ->required(),
                                TextInput::make('code')
                                    ->label(__('country.fields.region_code'))
                                    ->required(),
                            ])
                            ->columnSpanFull(),

                        TextInput::make('name')
                            ->label(__('country.fields.name'))
                            ->required()
                            ->maxLength(255)
                            ->placeholder(__('country.placeholders.name'))
                            ->columnSpan(1),

                        TextInput::make('code')
                            ->label(__('country.fields.code'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(3)
                            ->placeholder(__('country.placeholders.code'))
                            ->helperText(__('country.helpers.code'))
                            ->columnSpan(1),

                        Toggle::make('is_active')
                            ->label(__('country.fields.is_active'))
                            ->default(true)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make(__('country.sections.regional_config'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('currency_code')
                                    ->label(__('country.fields.currency_code'))
                                    ->required()
                                    ->searchable()
                                    ->options([
                                        'USD' => __('country.currencies.USD'),
                                        'EUR' => __('country.currencies.EUR'),
                                        'MXN' => __('country.currencies.MXN'),
                                        'GTQ' => __('country.currencies.GTQ'),
                                        'HNL' => __('country.currencies.HNL'),
                                        'NIO' => __('country.currencies.NIO'),
                                        'CRC' => __('country.currencies.CRC'),
                                        'PAB' => __('country.currencies.PAB'),
                                        'BRL' => __('country.currencies.BRL'),
                                        'ARS' => __('country.currencies.ARS'),
                                        'CLP' => __('country.currencies.CLP'),
                                        'COP' => __('country.currencies.COP'),
                                        'PEN' => __('country.currencies.PEN'),
                                    ])
                                    ->default('USD')
                                    ->columnSpan(1),

                                Select::make('timezone')
                                    ->label(__('country.fields.timezone'))
                                    ->required()
                                    ->searchable()
                                    ->options([
                                        'America/El_Salvador' => __('country.timezones.America/El_Salvador'),
                                        'America/Guatemala' => __('country.timezones.America/Guatemala'),
                                        'America/Tegucigalpa' => __('country.timezones.America/Tegucigalpa'),
                                        'America/Managua' => __('country.timezones.America/Managua'),
                                        'America/Costa_Rica' => __('country.timezones.America/Costa_Rica'),
                                        'America/Panama' => __('country.timezones.America/Panama'),
                                        'America/Mexico_City' => __('country.timezones.America/Mexico_City'),
                                        'America/Bogota' => __('country.timezones.America/Bogota'),
                                        'America/Lima' => __('country.timezones.America/Lima'),
                                        'America/Santiago' => __('country.timezones.America/Santiago'),
                                        'America/Argentina/Buenos_Aires' => __('country.timezones.America/Argentina/Buenos_Aires'),
                                        'America/Sao_Paulo' => __('country.timezones.America/Sao_Paulo'),
                                    ])
                                    ->default('America/El_Salvador')
                                    ->columnSpan(1),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('length_unit')
                                    ->label(__('country.fields.length_unit'))
                                    ->required()
                                    ->options([
                                        'cm' => __('country.length_units.cm'),
                                        'm' => __('country.length_units.m'),
                                        'in' => __('country.length_units.in'),
                                    ])
                                    ->default('cm')
                                    ->helperText(__('country.helpers.length_unit'))
                                    ->columnSpan(1),

                                Select::make('mass_unit')
                                    ->label(__('country.fields.mass_unit'))
                                    ->required()
                                    ->options([
                                        'kg' => __('country.mass_units.kg'),
                                        'g' => __('country.mass_units.g'),
                                        'lb' => __('country.mass_units.lb'),
                                    ])
                                    ->default('kg')
                                    ->helperText(__('country.helpers.mass_unit'))
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make(__('country.sections.pre_alert_calculation'))
                    ->description('Valores utilizados para calcular el costo de las pre-alertas')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('shipping_pound_value')
                                    ->label(__('country.fields.shipping_pound_value'))
                                    ->numeric()
                                    ->default(4.99)
                                    ->step(0.01)
                                    ->required()
                                    ->helperText(__('country.helpers.shipping_pound_value'))
                                    ->prefix('US$'),

                                TextInput::make('customs_management')
                                    ->label(__('country.fields.customs_management'))
                                    ->numeric()
                                    ->default(4.99)
                                    ->step(0.01)
                                    ->required()
                                    ->helperText(__('country.helpers.customs_management'))
                                    ->prefix('US$'),

                                TextInput::make('third_party_handling')
                                    ->label(__('country.fields.third_party_handling'))
                                    ->numeric()
                                    ->default(2.74)
                                    ->step(0.01)
                                    ->required()
                                    ->helperText(__('country.helpers.third_party_handling'))
                                    ->prefix('US$'),

                                TextInput::make('delivery_guarantee_percentage')
                                    ->label(__('country.fields.delivery_guarantee_percentage'))
                                    ->numeric()
                                    ->default(0.01)
                                    ->step(0.0001)
                                    ->required()
                                    ->helperText(__('country.helpers.delivery_guarantee'))
                                    ->suffix('%'),

                                TextInput::make('iva_cif_percentage')
                                    ->label(__('country.fields.iva_cif_percentage'))
                                    ->numeric()
                                    ->default(0.145)
                                    ->step(0.0001)
                                    ->required()
                                    ->helperText(__('country.helpers.iva_cif'))
                                    ->suffix('%'),

                                TextInput::make('dai_percentage')
                                    ->label(__('country.fields.dai_percentage'))
                                    ->numeric()
                                    ->nullable()
                                    ->step(0.0001)
                                    ->helperText(__('country.helpers.dai'))
                                    ->suffix('%'),

                                TextInput::make('dai_threshold')
                                    ->label(__('country.fields.dai_threshold'))
                                    ->numeric()
                                    ->default(300.00)
                                    ->step(0.01)
                                    ->required()
                                    ->helperText(__('country.helpers.dai_threshold'))
                                    ->prefix('US$'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
