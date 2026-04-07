<?php

namespace App\Filament\Admin\Resources\Customers\RelationManagers;

use App\Filament\Admin\Resources\PreAlertOrders\PreAlertOrderResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class PreAlertOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'preAlertOrders';

    protected static ?string $title = 'Prealertas';

    protected static ?string $modelLabel = 'prealerta';

    protected static ?string $pluralModelLabel = 'prealertas';

    protected static ?string $relatedResource = PreAlertOrderResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
            ]);
    }
}
