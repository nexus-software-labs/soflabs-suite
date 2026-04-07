<?php

namespace App\Filament\Admin\Resources\Customers\RelationManagers;

use App\Models\Core\CustomerAddress;
use App\Models\Core\GeoBoxfulMapping;
use App\Services\GeoNamesService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $title = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('address.title');
    }

    public static function getModelLabel(): string
    {
        return __('address.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('address.plural_model_label');
    }

    private function enrichWithBoxfulMapping(array $data): array
    {
        // Sincronizar country_code con country
        $data['country_code'] = $data['country'] ?? $data['country_code'] ?? 'SV';
        $countryCode = $data['country_code'];

        // Rellenar region, city, locality (labels) cuando faltan pero tenemos los códigos
        $data = $this->fillLabelsFromCodes($data, $countryCode);

        // Buscar mapping Boxful y asignar boxful_state_id, boxful_city_id
        $mapping = GeoBoxfulMapping::findByGeonames(
            $countryCode,
            $data['region_code'] ?? null,
            $data['locality_code'] ?? null
        );
        if ($mapping) {
            $data['boxful_state_id'] = $mapping->boxful_state_id;
            $data['boxful_city_id'] = $mapping->boxful_city_id;
            if (empty($data['latitude']) && $mapping->boxful_city_latitude) {
                $data['latitude'] = (float) $mapping->boxful_city_latitude;
                $data['longitude'] = (float) $mapping->boxful_city_longitude;
            }
        }

        // Manejar dirección predeterminada
        $customerId = $this->getOwnerRecord()->id;
        $isFirstAddress = ! CustomerAddress::where('customer_id', $customerId)->exists();
        $data['is_default'] = $isFirstAddress || ($data['is_default'] ?? false);

        if ($data['is_default']) {
            CustomerAddress::where('customer_id', $customerId)
                ->update(['is_default' => false]);
        }

        $data['customer_id'] = $customerId;
        if (empty($data['id'])) {
            $data['created_by'] = auth()->id();
        } else {
            $data['updated_by'] = auth()->id();
        }

        return $data;
    }

    /**
     * Rellena region, city, locality (labels) desde GeoNames cuando faltan pero hay códigos.
     */
    private function fillLabelsFromCodes(array $data, string $countryCode): array
    {
        $regionCode = $data['region_code'] ?? null;
        $cityCode = $data['city_code'] ?? null;
        $localityCode = $data['locality_code'] ?? null;

        if (empty($data['region']) && $regionCode) {
            $adm1 = GeoNamesService::getAdm1($countryCode);
            $found = collect($adm1)->first(fn ($r) => ($r['code'] ?? (string) $r['id']) === (string) $regionCode);
            if ($found) {
                $data['region'] = $found['name'];
            }
        }

        if (empty($data['city']) && $regionCode && $cityCode) {
            $adm2 = GeoNamesService::getAdm2($countryCode, (string) $regionCode);
            $found = collect($adm2)->first(fn ($c) => ($c['code'] ?? (string) $c['id']) === (string) $cityCode);
            if ($found) {
                $data['city'] = $found['name'];
            }
        }

        if (empty($data['locality']) && $regionCode && $cityCode && $localityCode) {
            $adm3 = GeoNamesService::getAdm3($countryCode, (string) $regionCode, (string) $cityCode);
            $found = collect($adm3)->first(fn ($l) => ($l['code'] ?? (string) $l['id']) === (string) $localityCode);
            if ($found) {
                $data['locality'] = $found['name'];
            }
        }

        return $data;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('latitude'),
                Hidden::make('longitude'),
                Hidden::make('boxful_state_id'),
                Hidden::make('boxful_city_id'),

                TextInput::make('name')
                    ->label(__('address.fields.name'))
                    ->required()
                    ->maxLength(100)
                    ->placeholder(__('address.placeholders.name'))
                    ->columnSpan(2),

                Select::make('country')
                    ->label(__('address.fields.country'))
                    ->options([
                        'SV' => __('address.countries.SV'),
                        'GT' => __('address.countries.GT'),
                    ])
                    ->searchable()
                    ->required()
                    ->native(false)
                    ->live()
                    ->default(fn ($record) => $record?->country ?? $record?->country_code)
                    ->afterStateUpdated(function ($state, callable $set) {
                        // Guardar también en country_code
                        $set('country_code', $state);

                        // Limpiar selects dependientes cuando cambia el país
                        $set('region_code', null);
                        $set('region', null);
                        $set('city_code', null);
                        $set('city', null);
                        $set('locality_code', null);
                        $set('locality', null);
                    })
                    ->columnSpan(1),

                Select::make('region_code')
                    ->label(__('address.fields.region_code'))
                    ->options(function (Get $get) {
                        $country = $get('country');
                        $record = $this->record ?? null;

                        // Si estamos editando y no hay país seleccionado, usar el país guardado
                        if (! $country && $record && $record->country) {
                            $country = $record->country;
                        }

                        // Si aún no hay país, retornar vacío (se cargará cuando se seleccione el país)
                        if (! $country) {
                            return [];
                        }

                        $options = [];

                        // Cargar todas las regiones del país
                        try {
                            $response = Http::get(url("/api/v1/geo/adm1/{$country}"));
                            if ($response->successful()) {
                                $data = $response->json();
                                $results = $data['results'] ?? [];
                                $options = collect($results)
                                    ->mapWithKeys(fn ($item) => [
                                        ($item['code'] ?? (string) $item['id']) => $item['name'],
                                    ])
                                    ->toArray();
                            }
                        } catch (\Exception $e) {
                            Log::error('Error cargando ADM1: '.$e->getMessage());
                        }

                        // Si estamos editando y el valor guardado no está en las opciones, agregarlo
                        if ($record && $record->region_code && ! isset($options[$record->region_code])) {
                            // Usar el nombre guardado o el código como fallback
                            $options[$record->region_code] = $record->region ?? $record->region_code;
                        }

                        return $options;
                    })
                    ->searchable()
                    ->live()
                    ->reactive()
                    ->required()
                    ->default(fn ($record) => $record?->region_code)
                    ->afterStateUpdated(function ($state, callable $set, Get $get) {
                        // Guardar el nombre de la región cuando se selecciona
                        $country = $get('country');
                        if ($state && $country) {
                            try {
                                $response = Http::get(url("/api/v1/geo/adm1/{$country}"));
                                if ($response->successful()) {
                                    $data = $response->json();
                                    $results = $data['results'] ?? [];
                                    // $selectedRegion = collect($results)->firstWhere('code', $state);
                                    $selectedRegion = collect($results)->first(
                                        fn ($item) => ($item['code'] ?? (string) $item['id']) === $state
                                    );
                                    if ($selectedRegion) {
                                        $set('region', $selectedRegion['name']);
                                    }
                                }
                            } catch (\Exception $e) {
                                Log::error('Error obteniendo nombre de región: '.$e->getMessage());
                            }
                        }

                        // Limpiar selects dependientes cuando cambia la región
                        $set('city_code', null);
                        $set('city', null);
                        $set('locality_code', null);
                        $set('locality', null);
                    })
                    ->columnSpan(1),

                Select::make('city_code')
                    ->label(__('address.fields.city_code'))
                    ->options(function (Get $get) {
                        $country = $get('country');
                        $regionCode = $get('region_code');
                        $record = $this->record ?? null;

                        // Si estamos editando y faltan valores pero hay guardados, usar los guardados
                        if ($record) {
                            if (! $country && $record->country) {
                                $country = $record->country;
                            }
                            if (! $regionCode && $record->region_code) {
                                $regionCode = $record->region_code;
                            }
                        }

                        // Si aún no hay país o región, retornar vacío
                        if (! $country || ! $regionCode) {
                            return [];
                        }

                        $options = [];

                        // Cargar todas las ciudades de la región
                        try {
                            $response = Http::get(url("/api/v1/geo/adm2/{$country}/{$regionCode}"));
                            if ($response->successful()) {
                                $data = $response->json();
                                $results = $data['results'] ?? [];
                                $options = collect($results)
                                    ->mapWithKeys(fn ($item) => [
                                        ($item['code'] ?? (string) $item['id']) => $item['name'],
                                    ])
                                    ->toArray();
                            }
                        } catch (\Exception $e) {
                            Log::error('Error cargando ADM2: '.$e->getMessage());
                        }

                        // Si estamos editando y el valor guardado no está en las opciones, agregarlo
                        if ($record && $record->city_code && ! isset($options[$record->city_code])) {
                            // Usar el nombre guardado o el código como fallback
                            $options[$record->city_code] = $record->city ?? $record->city_code;
                        }

                        return $options;
                    })
                    ->searchable()
                    ->live()
                    ->required()
                    ->reactive()
                    ->default(fn ($record) => $record?->city_code)
                    ->afterStateUpdated(function ($state, callable $set, Get $get) {
                        // Guardar el nombre de la ciudad cuando se selecciona
                        $country = $get('country');
                        $regionCode = $get('region_code');
                        if ($state && $country && $regionCode) {
                            try {
                                $response = Http::get(url("/api/v1/geo/adm2/{$country}/{$regionCode}"));
                                if ($response->successful()) {
                                    $data = $response->json();
                                    $results = $data['results'] ?? [];
                                    // $selectedCity = collect($results)->firstWhere('code', $state);
                                    $selectedCity = collect($results)->first(
                                        fn ($item) => ($item['code'] ?? (string) $item['id']) === $state
                                    );
                                    if ($selectedCity) {
                                        $set('city', $selectedCity['name']);
                                    }
                                }
                            } catch (\Exception $e) {
                                Log::error('Error obteniendo nombre de ciudad: '.$e->getMessage());
                            }
                        }

                        // Limpiar localidad cuando cambia la ciudad
                        $set('locality_code', null);
                        $set('locality', null);
                    })
                    ->columnSpan(1),

                Select::make('locality_code')
                    ->label(__('address.fields.locality_code'))
                    ->options(function (Get $get) {
                        $country = $get('country');
                        $regionCode = $get('region_code');
                        $cityCode = $get('city_code');
                        $record = $this->record ?? null;

                        // Si estamos editando y faltan valores pero hay guardados, usar los guardados
                        if ($record) {
                            if (! $country && $record->country) {
                                $country = $record->country;
                            }
                            if (! $regionCode && $record->region_code) {
                                $regionCode = $record->region_code;
                            }
                            if (! $cityCode && $record->city_code) {
                                $cityCode = $record->city_code;
                            }
                        }

                        // Si aún no hay país, región o ciudad, retornar vacío
                        if (! $country || ! $regionCode || ! $cityCode) {
                            return [];
                        }

                        $options = [];

                        // Cargar todas las localidades de la ciudad
                        try {
                            $response = Http::get(url("/api/v1/geo/adm3/{$country}/{$regionCode}/{$cityCode}"));
                            if ($response->successful()) {
                                $data = $response->json();
                                $results = $data['results'] ?? [];

                                if (empty($results)) {
                                    // Si no hay ADM3, pero estamos editando y hay un valor guardado, agregarlo
                                    if ($record && $record->locality_code) {
                                        $options[$record->locality_code] = $record->locality ?? $record->locality_code;
                                    }

                                    return $options;
                                }

                                $options = collect($results)
                                    ->mapWithKeys(fn ($item) => [
                                        ($item['code'] ?? (string) $item['id']) => $item['name'],
                                    ])
                                    ->toArray();
                            }
                        } catch (\Exception $e) {
                            Log::error('Error cargando ADM3: '.$e->getMessage());
                        }

                        // Si estamos editando y el valor guardado no está en las opciones, agregarlo
                        if ($record && $record->locality_code && ! isset($options[$record->locality_code])) {
                            // Usar el nombre guardado o el código como fallback
                            $options[$record->locality_code] = $record->locality ?? $record->locality_code;
                        }

                        return $options;
                    })
                    ->searchable()
                    ->live()
                    ->reactive()
                    ->default(fn ($record) => $record?->locality_code)
                    ->afterStateUpdated(function ($state, callable $set, Get $get) {
                        // Guardar el nombre de la localidad cuando se selecciona
                        $country = $get('country');
                        $regionCode = $get('region_code');
                        $cityCode = $get('city_code');
                        if ($state && $country && $regionCode && $cityCode) {
                            try {
                                $response = Http::get(url("/api/v1/geo/adm3/{$country}/{$regionCode}/{$cityCode}"));
                                if ($response->successful()) {
                                    $data = $response->json();
                                    $results = $data['results'] ?? [];
                                    // $selectedLocality = collect($results)->firstWhere('code', $state);
                                    $selectedLocality = collect($results)->first(
                                        fn ($item) => ($item['code'] ?? (string) $item['id']) === $state
                                    );
                                    if ($selectedLocality) {
                                        $set('locality', $selectedLocality['name']);
                                    }
                                }
                            } catch (\Exception $e) {
                                Log::error('Error obteniendo nombre de localidad: '.$e->getMessage());
                            }
                        }
                    })
                    ->disabled(fn (Get $get) => empty($get('country')) || empty($get('region_code')) || empty($get('city_code')))
                    ->columnSpan(1),

                TextInput::make('address')
                    ->label(__('address.fields.address'))
                    ->required()
                    ->maxLength(1000)
                    ->columnSpanFull(),

                Textarea::make('references')
                    ->label(__('address.fields.references'))
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpanFull(),

                TextInput::make('phone')
                    ->label(__('address.fields.phone'))
                    ->tel()
                    ->maxLength(20)
                    ->columnSpan(1),

                // Campos ocultos para guardar nombres y códigos
                Hidden::make('country_code'),
                Hidden::make('region'),
                Hidden::make('city'),
                Hidden::make('locality'),

                // Campos ocultos para coordenadas (se calcularán automáticamente)
                Hidden::make('latitude'),
                Hidden::make('longitude'),

                Toggle::make('is_default')
                    ->label(__('address.fields.is_default'))
                    ->columnSpan(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('address.table.name'))
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('country')
                    ->label(__('address.table.country'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'SV' => __('address.countries.SV'),
                        'GT' => __('address.countries.GT'),
                        'HN' => __('address.countries.HN'),
                        'NI' => __('address.countries.NI'),
                        'CR' => __('address.countries.CR'),
                        'PA' => __('address.countries.PA'),
                        'US' => __('address.countries.US'),
                        'MX' => __('address.countries.MX'),
                        default => $state,
                    }),

                TextColumn::make('city')
                    ->label(__('address.table.city'))
                    ->searchable(),

                TextColumn::make('address')
                    ->label(__('address.table.address'))
                    ->limit(50)
                    ->wrap(),

                TextColumn::make('phone')
                    ->label(__('address.table.phone'))
                    ->icon('heroicon-m-phone'),

                IconColumn::make('is_default')
                    ->label(__('address.table.is_default'))
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('created_at')
                    ->label(__('address.table.created_at'))
                    ->dateTime('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_default')
                    ->label(__('address.filters.is_default'))
                    ->placeholder(__('address.filters.all'))
                    ->trueLabel(__('address.filters.yes'))
                    ->falseLabel(__('address.filters.no')),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(fn (array $data): array => $this->enrichWithBoxfulMapping($data)),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateFormDataUsing(fn (array $data): array => $this->enrichWithBoxfulMapping($data)),
                // DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('is_default', 'desc');
    }
}
