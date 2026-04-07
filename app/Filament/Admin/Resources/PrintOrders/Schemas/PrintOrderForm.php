<?php

namespace App\Filament\Admin\Resources\PrintOrders\Schemas;

use App\Models\User;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class PrintOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('print_order.form.sections.customer_info'))
                    ->schema([
                        Select::make('user_id')
                            ->label(__('print_order.form.fields.customer'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->nullable()
                            ->columnSpanFull()
                            ->placeholder(__('print_order.form.placeholders.customer_select'))
                            ->helperText(__('print_order.form.helpers.customer_empty'))
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $user = User::find($state);
                                    if ($user) {
                                        $set('customer_name', $user->name);
                                        $set('customer_email', $user->email);
                                        $set('customer_phone', null);
                                    }
                                } else {
                                    $set('customer_name', null);
                                    $set('customer_email', null);
                                    $set('customer_phone', null);
                                }
                            })
                            ->visible(function ($get, $record) {
                                // Si no hay registro o el registro no tiene ID, es creación: siempre visible
                                if (! $record || ! $record->getKey()) {
                                    return true;
                                }

                                // Si hay registro con ID (edición): solo visible si tiene user_id
                                return ! is_null($get('user_id'));
                            }),
                        Placeholder::make('cliente_sin_casillero_info')
                            ->label(__('print_order.form.fields.customer'))
                            ->content(function ($get) {
                                return new HtmlString(
                                    '<div class="p-3 bg-gray-50 border border-gray-200 rounded-lg dark:bg-white/5 dark:border-white/10">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            📋 '.e(__('print_order.form.no_locker.title')).'
                                        </p>
                                    </div>'
                                );
                            })
                            ->visible(function ($get, $record) {
                                // Solo visible en edición cuando el registro tiene ID y user_id es null
                                return $record && $record->getKey() && is_null($get('user_id'));
                            })
                            ->columnSpanFull(),
                        Placeholder::make('cliente_sin_casillero_notice')
                            ->label('')
                            ->content(function ($get) {
                                return new HtmlString(
                                    '<div class="p-3 bg-amber-50 border border-amber-200 rounded-lg dark:bg-amber-500/10 dark:border-amber-500/30">
                                        <p class="text-sm font-medium text-amber-900 dark:text-amber-200">
                                            ⚠️ '.e(__('print_order.form.no_locker.notice')).'
                                        </p>
                                    </div>'
                                );
                            })
                            ->visible(function ($get, $record) {
                                // Solo visible en creación cuando user_id es null
                                return (! $record || ! $record->getKey()) && ! $get('user_id');
                            })
                            ->columnSpanFull(),
                        TextInput::make('customer_name')
                            ->label(__('print_order.form.fields.customer_name'))
                            ->required(fn ($get, $record) => (! $record || ! $record->getKey()) && ! $get('user_id'))
                            ->visible(function ($get, $record) {
                                // En creación: visible si user_id es null
                                if (! $record || ! $record->getKey()) {
                                    return ! $get('user_id');
                                }

                                // En edición: visible si user_id es null
                                return is_null($get('user_id'));
                            }),
                        TextInput::make('customer_email')
                            ->label(__('print_order.form.fields.customer_email'))
                            ->email()
                            ->required(fn ($get, $record) => (! $record || ! $record->getKey()) && ! $get('user_id'))
                            ->visible(function ($get, $record) {
                                if (! $record || ! $record->getKey()) {
                                    return ! $get('user_id');
                                }

                                return is_null($get('user_id'));
                            }),
                        TextInput::make('customer_phone')
                            ->label(__('print_order.form.fields.customer_phone'))
                            ->tel()
                            ->visible(function ($get, $record) {
                                if (! $record || ! $record->getKey()) {
                                    return ! $get('user_id');
                                }

                                return is_null($get('user_id'));
                            }),
                    ])->columns(2),

                Section::make(__('print_order.form.sections.print_specs'))
                    ->schema([
                        Select::make('print_type')
                            ->label(__('print_order.form.fields.print_type'))
                            ->options(__('print_order.print_type'))
                            ->required()
                            ->default('bw'),
                        Select::make('paper_size')
                            ->label(__('print_order.form.fields.paper_size'))
                            ->options(__('print_order.form.paper_size'))
                            ->required()
                            ->default('letter'),
                        Select::make('paper_type')
                            ->label(__('print_order.form.fields.paper_type'))
                            ->options(__('print_order.form.paper_type'))
                            ->required()
                            ->default('bond'),
                        Select::make('orientation')
                            ->label(__('print_order.form.fields.orientation'))
                            ->options(__('print_order.form.orientation'))
                            ->required()
                            ->default('portrait'),
                        TextInput::make('page_range')
                            ->label(__('print_order.form.fields.page_range'))
                            ->default('all')
                            ->helperText(__('print_order.form.helpers.page_range')),
                        TextInput::make('copies')
                            ->label(__('print_order.form.fields.copies'))
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required(),
                        TextInput::make('pages_count')
                            ->label(__('print_order.form.fields.pages_count'))
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        Toggle::make('double_sided')
                            ->label(__('print_order.form.fields.double_sided')),
                        Toggle::make('binding')
                            ->label(__('print_order.form.fields.binding')),
                    ])->columns(3),

                Section::make(__('print_order.form.sections.delivery'))
                    ->schema([
                        Select::make('delivery_method')
                            ->label(__('print_order.form.fields.delivery_method'))
                            ->options(__('print_order.form.delivery_method_options'))
                            ->required()
                            ->default('pickup')
                            ->reactive(),
                        Select::make('branch_id')
                            ->label(__('print_order.form.fields.branch'))
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->visible(fn ($get) => $get('delivery_method') === 'pickup'),
                        Textarea::make('delivery_address')
                            ->label(__('print_order.form.fields.delivery_address'))
                            ->rows(2)
                            ->visible(fn ($get) => $get('delivery_method') === 'delivery'),
                        TextInput::make('delivery_phone')
                            ->label(__('print_order.form.fields.delivery_phone'))
                            ->tel()
                            ->visible(fn ($get) => $get('delivery_method') === 'delivery'),
                        Textarea::make('delivery_notes')
                            ->label(__('print_order.form.fields.delivery_notes'))
                            ->rows(2)
                            ->visible(fn ($get) => $get('delivery_method') === 'delivery'),
                    ])->columns(2),

                Section::make(__('print_order.form.sections.status_payment'))
                    ->schema([
                        Select::make('status')
                            ->label(__('print_order.form.fields.status'))
                            ->options(__('print_order.form.status_options'))
                            ->required()
                            ->default('pending'),
                        Select::make('payment_method')
                            ->label(__('print_order.form.fields.payment_method'))
                            ->options(__('print_order.form.payment_method'))
                            ->required()
                            ->default('cash'),
                        Select::make('payment_status')
                            ->label(__('print_order.form.fields.payment_status'))
                            ->options(__('print_order.payment_status'))
                            ->required()
                            ->default('pending'),
                    ])->columns(3),

                Section::make(__('print_order.form.sections.prices'))
                    ->schema([
                        TextInput::make('price_per_page')
                            ->label(__('print_order.form.fields.price_per_page'))
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        TextInput::make('binding_price')
                            ->label(__('print_order.form.fields.binding_price'))
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        TextInput::make('double_sided_cost')
                            ->label(__('print_order.form.fields.double_sided_cost'))
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        TextInput::make('subtotal')
                            ->label(__('print_order.form.fields.subtotal'))
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        TextInput::make('delivery_cost')
                            ->label(__('print_order.form.fields.delivery_cost'))
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        TextInput::make('tax')
                            ->label(__('print_order.form.fields.tax'))
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        TextInput::make('total')
                            ->label(__('print_order.form.fields.total'))
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                    ])->columns(3),

                Section::make(__('print_order.form.sections.notes'))
                    ->schema([
                        Textarea::make('customer_notes')
                            ->label(__('print_order.form.fields.customer_notes'))
                            ->rows(3),
                        Textarea::make('admin_notes')
                            ->label(__('print_order.form.fields.admin_notes'))
                            ->rows(3),
                    ])->columns(2),
            ]);
    }
}
