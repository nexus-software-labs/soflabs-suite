<?php

declare(strict_types=1);

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $guarded = ['id'];

    public static function getDefaultConfig(): array
    {
        return [
            'racks_count' => 1,
            'segments_per_rack' => 10,
            'auto_assignment_type' => 'none',
        ];
    }

    public function generateLocations(): void
    {
        // Implementar cuando exista WarehouseLocation y la lógica de layout.
    }
}
