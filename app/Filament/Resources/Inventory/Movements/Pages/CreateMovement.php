<?php

namespace App\Filament\Resources\Inventory\Movements\Pages;

use App\Filament\Resources\Inventory\Movements\MovementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMovement extends CreateRecord
{
    protected static string $resource = MovementResource::class;
}
