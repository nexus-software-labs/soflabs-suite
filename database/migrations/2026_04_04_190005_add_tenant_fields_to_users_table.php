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
        Schema::table('users', function (Blueprint $table) {
            $table->string('tenant_id')->nullable();
            $table->foreignUlid('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->boolean('is_tenant_admin')->default(false);
            $table->boolean('is_super_admin')->default(false);
            $table->timestamp('last_seen_at')->nullable();
            $table->json('settings')->nullable();
            $table->string('avatar')->nullable();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropForeign(['branch_id']);
            $table->dropColumn([
                'tenant_id',
                'branch_id',
                'is_tenant_admin',
                'is_super_admin',
                'last_seen_at',
                'settings',
                'avatar',
            ]);
        });
    }
};
