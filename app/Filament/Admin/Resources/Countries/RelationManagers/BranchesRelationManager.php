<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Countries\RelationManagers;

use App\Filament\Admin\Resources\Branches\BranchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class BranchesRelationManager extends RelationManager
{
    protected static string $relationship = 'branches';

    protected static ?string $relatedResource = BranchResource::class;

    protected static ?string $title = 'Sucursales';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withoutGlobalScopes())
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
