<?php

namespace App\Filament\Admin\Resources\Plans\Schemas;

use App\Models\Plan;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del plan')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                $set('slug', Str::slug($state ?? ''));
                            }),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->alphaDash()
                            ->unique(
                                table: (new Plan)->getTable(),
                                column: 'slug',
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule): Unique => $rule->whereNull('deleted_at'),
                            ),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                        TextInput::make('price_monthly')
                            ->label('Precio mensual')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('$'),
                        TextInput::make('price_yearly')
                            ->label('Precio anual')
                            ->helperText('Dejar vacío si no aplica')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('$')
                            ->nullable(),
                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true)
                            ->required(),
                        CheckboxList::make('modules')
                            ->label('Módulos incluidos')
                            ->options([
                                'inventario' => 'Inventario',
                                'logistica' => 'Logística',
                                'impresiones' => 'Impresiones',
                            ])
                            ->columns(3)
                            ->default([]),
                        KeyValue::make('limits')
                            ->label('Límites')
                            ->keyLabel('Clave')
                            ->valueLabel('Valor')
                            ->keyPlaceholder('max_branches')
                            ->valuePlaceholder('Ej. 5')
                            ->addActionLabel('Añadir límite')
                            ->nullable()
                            ->dehydrateStateUsing(function (?array $state): ?array {
                                if ($state === null || $state === []) {
                                    return null;
                                }

                                return $state;
                            })
                            ->afterStateHydrated(function (KeyValue $component, $state): void {
                                if ($state === null) {
                                    $component->state([]);
                                }
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
