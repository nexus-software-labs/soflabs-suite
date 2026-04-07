<?php

namespace App\Filament\Admin\Resources\Regions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RegionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('region.sections.general_info'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('region.fields.name'))
                            ->required()
                            ->maxLength(255)
                            ->placeholder(__('region.placeholders.name'))
                            ->columnSpanFull(),

                        TextInput::make('code')
                            ->label(__('region.fields.code'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder(__('region.placeholders.code'))
                            ->helperText(__('region.helpers.code'))
                            ->columnSpan(1),

                        Select::make('franchisee_id')
                            ->label(__('region.fields.franchisee_id'))
                            ->relationship('franchisee', 'name', function ($query) {
                                $query->whereHas('roles', function ($q) {
                                    $q->whereIn('name', ['franchisee', 'regional_admin', 'super_admin']);
                                })->where('is_active', true);
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder(__('region.placeholders.franchisee'))
                            ->helperText(__('region.helpers.franchisee'))
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name.' ('.$record->roles->pluck('name')->join(', ').')')
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label(__('region.fields.is_active'))
                            ->default(true)
                            ->helperText(__('region.helpers.is_active'))
                            ->columnSpan(1),

                        Textarea::make('description')
                            ->label(__('region.fields.description'))
                            ->rows(3)
                            ->placeholder(__('region.placeholders.description'))
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

            ]);
    }
}
