<?php

namespace App\Filament\Admin\Resources\Customers\Schemas;

use App\Models\Branch;
use App\Models\Core\CustomerTier;
use App\Services\DocumentValidationService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use ToneGabes\BetterOptions\Forms\Components\RadioCards;

class CustomerForm
{
    /**
     * Obtener el schema como array de componentes (para usar en Actions)
     */
    public static function getSchema(): array
    {
        return [
            Section::make(__('customer.sections.user_info'))
                ->schema([
                    TextInput::make('user_name')
                        ->label(__('customer.fields.user_name'))
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    TextInput::make('user_email')
                        ->label(__('customer.fields.user_email'))
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique('users', 'email', ignoreRecord: true)
                        ->columnSpan(1),

                    TextInput::make('password')
                        ->label(__('customer.fields.password'))
                        ->password()
                        ->required(fn ($operation) => $operation === 'create')
                        ->minLength(8)
                        ->confirmed()
                        ->columnSpan(1),

                    TextInput::make('password_confirmation')
                        ->label(__('customer.fields.password_confirmation'))
                        ->password()
                        ->required(fn ($operation) => $operation === 'create')
                        ->same('password')
                        ->columnSpan(1),
                ])
                ->columnSpanFull()
                ->visible(fn ($operation) => $operation === 'create'),

            Section::make(__('customer.sections.customer_info'))
                ->schema([
                    TextInput::make('locker_code')
                        ->label(__('customer.fields.locker_code'))
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->columnSpan(1),

                    Select::make('country')
                        ->label(__('customer.fields.country'))
                        ->options([
                            'SV' => __('customer.countries.SV'),
                            'GT' => __('customer.countries.GT'),
                            'HN' => __('customer.countries.HN'),
                            'NI' => __('customer.countries.NI'),
                            'CR' => __('customer.countries.CR'),
                            'PA' => __('customer.countries.PA'),
                            'US' => __('customer.countries.US'),
                            'MX' => __('customer.countries.MX'),
                        ])
                        ->default('SV')
                        ->searchable()
                        ->native(false)
                        ->live()
                        ->required()
                        ->afterStateUpdated(function ($state, callable $set) {
                            // Limpiar selects dependientes cuando cambia el país
                            $set('branch_id', null);
                            $set('document_type', null);
                            $set('cedula_rnc', null);
                        })
                        ->columnSpan(1),

                    Select::make('language')
                        ->label(__('customer.fields.language'))
                        ->options([
                            'es' => __('customer.languages.es'),
                            'en' => __('customer.languages.en'),
                        ])
                        ->default('es')
                        ->native(false)
                        ->columnSpan(1),

                    Select::make('document_type')
                        ->label(__('customer.fields.document_type'))
                        ->options(function (Get $get) {
                            $country = $get('country');
                            if (! $country) {
                                return [];
                            }

                            $types = DocumentValidationService::getDocumentTypes($country);

                            return collect($types)->pluck('name', 'code')->toArray();
                        })
                        ->searchable()
                        ->live()
                        ->required()
                        ->afterStateUpdated(function ($state, callable $set) {
                            // Limpiar cédula cuando cambia el tipo de documento
                            $set('cedula_rnc', null);
                        })
                        ->helperText(function (Get $get) {
                            $country = $get('country');
                            $documentType = $get('document_type');

                            if (! $country || ! $documentType) {
                                return null;
                            }

                            $types = DocumentValidationService::getDocumentTypes($country);
                            $selectedType = collect($types)->firstWhere('code', $documentType);

                            return $selectedType ? $selectedType['description'] : null;
                        })
                        ->columnSpan(1),

                    TextInput::make('cedula_rnc')
                        ->label(__('customer.fields.cedula_rnc'))
                        ->maxLength(function (Get $get) {
                            $country = $get('country');
                            $documentType = $get('document_type');

                            if (! $country || ! $documentType) {
                                return 255;
                            }

                            $types = DocumentValidationService::getDocumentTypes($country);
                            $selectedType = collect($types)->firstWhere('code', $documentType);

                            return $selectedType['length'] ?? 255;
                        })
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set, Get $get) {
                            // Aplicar máscara al documento
                            $country = $get('country');
                            $documentType = $get('document_type');

                            if (! $state || ! $country || ! $documentType) {
                                return;
                            }

                            $types = DocumentValidationService::getDocumentTypes($country);
                            $selectedType = collect($types)->firstWhere('code', $documentType);

                            if ($selectedType && $selectedType['format']) {
                                $masked = self::applyDocumentMask($state, $selectedType['format']);
                                if ($masked !== $state) {
                                    $set('cedula_rnc', $masked);
                                }
                            }
                        })
                        ->rules(function (Get $get) {
                            return [
                                function ($attribute, $value, $fail) use ($get) {
                                    $country = $get('country');
                                    $documentType = $get('document_type');

                                    if (! $value || ! $country || ! $documentType) {
                                        return;
                                    }

                                    if (! DocumentValidationService::validate($value, $country, $documentType)) {
                                        $types = DocumentValidationService::getDocumentTypes($country);
                                        $selectedType = collect($types)->firstWhere('code', $documentType);
                                        $format = $selectedType['format'] ?? 'formato válido';

                                        $fail(__('customer.helpers.document_invalid', ['format' => $format]));
                                    }
                                },
                            ];
                        })
                        ->placeholder(function (Get $get) {
                            $country = $get('country');
                            $documentType = $get('document_type');

                            if (! $country || ! $documentType) {
                                return __('customer.placeholders.select_country_document');
                            }

                            $types = DocumentValidationService::getDocumentTypes($country);
                            $selectedType = collect($types)->firstWhere('code', $documentType);

                            return $selectedType['format'] ?? __('customer.placeholders.document_number');
                        })
                        ->helperText(function (Get $get) {
                            $country = $get('country');
                            $documentType = $get('document_type');

                            if (! $country || ! $documentType) {
                                return null;
                            }

                            $types = DocumentValidationService::getDocumentTypes($country);
                            $selectedType = collect($types)->firstWhere('code', $documentType);

                            return $selectedType ? __('customer.helpers.document_format', ['format' => $selectedType['format']]) : null;
                        })
                        ->columnSpan(1),

                    Select::make('branch_id')
                        ->label(__('customer.fields.branch_id'))
                        ->options(function (Get $get) {
                            $country = $get('country');
                            if (! $country) {
                                return [];
                            }

                            return Branch::query()
                                ->withoutGlobalScopes()
                                ->where(function ($query) use ($country) {
                                    $query->where('country', $country)
                                        ->orWhereHas('countryModel', fn ($q) => $q->where('code', $country));
                                })
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->columnSpan(1),

                    RadioCards::make('customer_tier_id')
                        ->label('Categoría de Cliente')
                        ->options(function () {
                            $tiers = CustomerTier::where('is_active', true)
                                ->orderBy('priority', 'desc')
                                ->get();

                            $options = [];
                            foreach ($tiers as $tier) {
                                $options[$tier->id] = $tier->name;
                            }

                            return $options;
                        })
                        ->descriptions(function () {
                            $tiers = CustomerTier::where('is_active', true)
                                ->orderBy('priority', 'desc')
                                ->get();

                            $descriptions = [];
                            foreach ($tiers as $tier) {
                                $descriptions[$tier->id] = $tier->description ?? '';
                            }

                            return $descriptions;
                        })
                        ->icons(function () {
                            $tiers = CustomerTier::where('is_active', true)
                                ->orderBy('priority', 'desc')
                                ->get();
                            $icons = [];
                            foreach ($tiers as $tier) {
                                $icons[$tier->id] = self::normalizeTierIcon($tier->icon ?? 'user');
                            }

                            return $icons;
                        })
                        ->helperText('Selecciona la categoría del cliente (VIP, Premium, etc.)')
                        ->columns(2)
                        ->nullable()
                        ->columnSpanFull(),

                    DatePicker::make('birth_date')
                        ->label(__('customer.fields.birth_date'))
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->maxDate(now())
                        ->columnSpan(1),
                ])
                ->columnSpanFull(),

            Section::make(__('customer.sections.contact_info'))
                ->schema([
                    TextInput::make('secundary_email')
                        ->label(__('customer.fields.secundary_email'))
                        ->email()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    TextInput::make('phone')
                        ->label(__('customer.fields.phone'))
                        ->tel()
                        ->maxLength(255)
                        ->columnSpan(1),

                    TextInput::make('home_phone')
                        ->label(__('customer.fields.home_phone'))
                        ->tel()
                        ->maxLength(255)
                        ->columnSpan(1),

                    TextInput::make('office_phone')
                        ->label(__('customer.fields.office_phone'))
                        ->tel()
                        ->maxLength(255)
                        ->columnSpan(1),

                    TextInput::make('fax')
                        ->label(__('customer.fields.fax'))
                        ->tel()
                        ->maxLength(255)
                        ->columnSpan(1),
                ])
                ->columnSpanFull(),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::getSchema());
    }

    /**
     * Aplicar máscara a documento (similar a la función en React)
     */
    protected static function applyDocumentMask(string $value, string $mask): string
    {
        if (! $value || ! $mask) {
            return $value;
        }

        // Limpiar valor: solo números y letras
        $cleanValue = strtoupper(preg_replace('/[^0-9A-Z]/i', '', $value));
        $maskedValue = '';
        $valueIndex = 0;

        // Aplicar máscara
        for ($i = 0; $i < strlen($mask) && $valueIndex < strlen($cleanValue); $i++) {
            if ($mask[$i] === '0' || $mask[$i] === 'A') {
                $maskedValue .= $cleanValue[$valueIndex];
                $valueIndex++;
            } else {
                $maskedValue .= $mask[$i];
            }
        }

        return $maskedValue;
    }

    /**
     * Convierte nombres cortos de icono (del seeder/BD) a formato Heroicon para BladeUI.
     */
    protected static function normalizeTierIcon(?string $icon): string
    {
        if (blank($icon)) {
            return 'heroicon-o-user';
        }
        if (str_starts_with($icon, 'heroicon-')) {
            return $icon;
        }
        $map = [
            'star' => 'heroicon-o-star',
            'sparkles' => 'heroicon-o-sparkles',
            'user' => 'heroicon-o-user',
            'user-circle' => 'heroicon-o-user-circle',
        ];

        return $map[$icon] ?? 'heroicon-o-'.str_replace('_', '-', $icon);
    }
}
