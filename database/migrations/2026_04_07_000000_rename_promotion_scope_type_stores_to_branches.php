<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('promotions')) {
            DB::table('promotions')->where('scope_type', 'stores')->update(['scope_type' => 'branches']);
        }

        if (Schema::hasTable('customer_tier_benefits')) {
            DB::table('customer_tier_benefits')->where('scope_type', 'stores')->update(['scope_type' => 'branches']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('promotions')) {
            DB::table('promotions')->where('scope_type', 'branches')->update(['scope_type' => 'stores']);
        }

        if (Schema::hasTable('customer_tier_benefits')) {
            DB::table('customer_tier_benefits')->where('scope_type', 'branches')->update(['scope_type' => 'stores']);
        }
    }
};
