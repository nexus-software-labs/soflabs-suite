<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignUlid('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->enum('db_mode', ['shared', 'schema', 'dedicated'])->default('shared');
            $table->boolean('is_active')->default(true);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscribed_at')->nullable();
            $table->string('company_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('country', 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn([
                'plan_id',
                'db_mode',
                'is_active',
                'trial_ends_at',
                'subscribed_at',
                'company_name',
                'phone',
                'country',
            ]);
        });
    }
};
