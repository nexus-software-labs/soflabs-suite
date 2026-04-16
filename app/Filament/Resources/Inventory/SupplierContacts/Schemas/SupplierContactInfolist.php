<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\SupplierContacts\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierContactInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contacto proveedor')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('supplier.name')->label('Proveedor')->placeholder('—'),
                                TextEntry::make('name')->label('Nombre'),
                                TextEntry::make('job_title')->label('Cargo')->placeholder('—'),
                                TextEntry::make('contact_type')->label('Tipo contacto')->badge(),
                                TextEntry::make('email')->label('Email')->placeholder('—'),
                                TextEntry::make('phone')->label('Teléfono')->placeholder('—'),
                                IconEntry::make('is_primary')->label('Principal')->boolean(),
                                TextEntry::make('status')->label('Estado')->badge(),
                            ]),
                    ]),
            ]);
    }
}
