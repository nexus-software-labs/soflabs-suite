<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer_tier_benefits')) {
            return;
        }

        if (Schema::hasTable('customer_tier_benefit_branches')) {
            return;
        }

        Schema::create('customer_tier_benefit_branches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_tier_benefit_id')
                ->constrained('customer_tier_benefits')
                ->cascadeOnDelete();
            $table->foreignUlid('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['customer_tier_benefit_id', 'branch_id'], 'ctb_branch_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_tier_benefit_branches');
    }
};
