<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table): void {
                if (Schema::hasColumn('customers', 'store_id')) {
                    try {
                        $table->dropForeign(['store_id']);
                    } catch (Throwable) {
                        // SQLite u otros drivers pueden no registrar el FK con el mismo nombre
                    }
                    $table->dropColumn('store_id');
                }
                if (! Schema::hasColumn('customers', 'branch_id')) {
                    $table->ulid('branch_id')->nullable()->after('user_id');
                }
            });
            Schema::table('customers', function (Blueprint $table): void {
                if (Schema::hasColumn('customers', 'branch_id')) {
                    $table->foreign('branch_id')
                        ->references('id')
                        ->on('branches')
                        ->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('print_orders')) {
            Schema::table('print_orders', function (Blueprint $table): void {
                if (Schema::hasColumn('print_orders', 'store_id')) {
                    try {
                        $table->dropForeign(['store_id']);
                    } catch (Throwable) {
                    }
                    $table->dropColumn('store_id');
                }
                if (! Schema::hasColumn('print_orders', 'branch_id')) {
                    $table->ulid('branch_id')->nullable()->after('delivery_method');
                }
            });
            Schema::table('print_orders', function (Blueprint $table): void {
                if (Schema::hasColumn('print_orders', 'branch_id')) {
                    $table->foreign('branch_id')
                        ->references('id')
                        ->on('branches')
                        ->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('print_orders')) {
            Schema::table('print_orders', function (Blueprint $table): void {
                if (Schema::hasColumn('print_orders', 'branch_id')) {
                    $table->dropForeign(['branch_id']);
                    $table->dropColumn('branch_id');
                }
                if (! Schema::hasColumn('print_orders', 'store_id')) {
                    $table->foreignId('store_id')->nullable();
                }
            });
        }

        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table): void {
                if (Schema::hasColumn('customers', 'branch_id')) {
                    $table->dropForeign(['branch_id']);
                    $table->dropColumn('branch_id');
                }
                if (! Schema::hasColumn('customers', 'store_id')) {
                    $table->foreignId('store_id')->nullable();
                }
            });
        }
    }
};
