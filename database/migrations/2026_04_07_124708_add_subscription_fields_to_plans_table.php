<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            if (! Schema::hasColumn('plans', 'price')) {
                $table->decimal('price', 10, 2)->default(0)->after('price_yearly');
            }

            if (! Schema::hasColumn('plans', 'signup_fee')) {
                $table->decimal('signup_fee', 10, 2)->default(0)->after('price');
            }

            if (! Schema::hasColumn('plans', 'currency')) {
                $table->string('currency', 3)->default('USD')->after('signup_fee');
            }

            if (! Schema::hasColumn('plans', 'trial_period')) {
                $table->unsignedSmallInteger('trial_period')->default(0)->after('currency');
            }

            if (! Schema::hasColumn('plans', 'trial_interval')) {
                $table->string('trial_interval')->default('day')->after('trial_period');
            }

            if (! Schema::hasColumn('plans', 'invoice_period')) {
                $table->unsignedSmallInteger('invoice_period')->default(1)->after('trial_interval');
            }

            if (! Schema::hasColumn('plans', 'invoice_interval')) {
                $table->string('invoice_interval')->default('month')->after('invoice_period');
            }

            if (! Schema::hasColumn('plans', 'grace_period')) {
                $table->unsignedSmallInteger('grace_period')->default(7)->after('invoice_interval');
            }

            if (! Schema::hasColumn('plans', 'grace_interval')) {
                $table->string('grace_interval')->default('day')->after('grace_period');
            }

            if (! Schema::hasColumn('plans', 'prorate_day')) {
                $table->unsignedTinyInteger('prorate_day')->nullable()->after('grace_interval');
            }

            if (! Schema::hasColumn('plans', 'prorate_period')) {
                $table->unsignedTinyInteger('prorate_period')->nullable()->after('prorate_day');
            }

            if (! Schema::hasColumn('plans', 'prorate_extend_due')) {
                $table->unsignedTinyInteger('prorate_extend_due')->nullable()->after('prorate_period');
            }

            if (! Schema::hasColumn('plans', 'active_subscribers_limit')) {
                $table->unsignedSmallInteger('active_subscribers_limit')->nullable()->after('prorate_extend_due');
            }

            if (! Schema::hasColumn('plans', 'sort_order')) {
                $table->unsignedSmallInteger('sort_order')->default(0)->after('active_subscribers_limit');
            }

            if (! Schema::hasColumn('plans', 'features')) {
                $table->json('features')->nullable()->after('limits');
            }
        });

        DB::table('plans')
            ->whereNull('price')
            ->update(['price' => DB::raw('COALESCE(price_monthly, 0)')]);
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            $table->dropColumn([
                'price',
                'signup_fee',
                'currency',
                'trial_period',
                'trial_interval',
                'invoice_period',
                'invoice_interval',
                'grace_period',
                'grace_interval',
                'prorate_day',
                'prorate_period',
                'prorate_extend_due',
                'active_subscribers_limit',
                'sort_order',
                'features',
            ]);
        });
    }
};
