<?php

namespace App\Filament\Admin\Resources\CustomerTiers\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CustomerTierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('customer_tier.sections.general_info'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('customer_tier.fields.name'))
                            ->required()
                            ->maxLength(255)
                            ->placeholder(__('customer_tier.placeholders.name'))
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (! $state) {
                                    return;
                                }
                                $set('slug', Str::slug($state));
                            }),

                        TextInput::make('slug')
                            ->label(__('customer_tier.fields.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder(__('customer_tier.placeholders.slug'))
                            ->helperText(__('customer_tier.helpers.slug'))
                            ->disabled(fn ($operation) => $operation === 'edit'),

                        Textarea::make('description')
                            ->label(__('customer_tier.fields.description'))
                            ->rows(3)
                            ->placeholder(__('customer_tier.placeholders.description'))
                            ->columnSpanFull(),

                        ColorPicker::make('color')
                            ->label(__('customer_tier.fields.color'))
                            ->default('#6B7280')
                            ->helperText(__('customer_tier.helpers.color')),

                        TextInput::make('icon')
                            ->label(__('customer_tier.fields.icon'))
                            ->maxLength(255)
                            ->placeholder(__('customer_tier.placeholders.icon'))
                            ->helperText(__('customer_tier.helpers.icon')),

                        TextInput::make('priority')
                            ->label(__('customer_tier.fields.priority'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->helperText(__('customer_tier.helpers.priority')),

                        Toggle::make('is_active')
                            ->label(__('customer_tier.fields.is_active'))
                            ->default(true)
                            ->helperText(__('customer_tier.helpers.is_active')),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
