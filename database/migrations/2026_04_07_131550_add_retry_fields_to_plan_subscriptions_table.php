<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            if (! Schema::hasColumn('subscriptions', 'retry_count')) {
                $table->unsignedSmallInteger('retry_count')->default(0)->after('payment_status');
            }

            if (! Schema::hasColumn('subscriptions', 'last_retry_at')) {
                $table->timestamp('last_retry_at')->nullable()->after('retry_count');
            }

            if (! Schema::hasColumn('subscriptions', 'next_retry_at')) {
                $table->timestamp('next_retry_at')->nullable()->after('last_retry_at')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->dropColumn(['retry_count', 'last_retry_at', 'next_retry_at']);
        });
    }
};
