<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('laravel-subscriptions.tables.subscriptions'), function (Blueprint $table): void {
            $table->id();

            $table->string('tenant_id')->index();
            $table->string('subscriber_type');
            $table->string('subscriber_id');
            $table->foreignIdFor(config('laravel-subscriptions.models.plan'));
            $table->json('name');
            $table->string('slug')->unique();
            $table->json('description')->nullable();
            $table->string('timezone')->nullable();

            $table->dateTime('trial_ends_at')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->dateTime('cancels_at')->nullable();
            $table->dateTime('canceled_at')->nullable();
            $table->enum('status', ['active', 'past_due', 'suspended', 'canceled'])->default('active')->index();
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->dateTime('next_billing_at')->nullable()->index();
            $table->dateTime('grace_ends_at')->nullable();
            $table->dateTime('suspended_at')->nullable();
            $table->string('gateway_customer_ref')->nullable();
            $table->string('gateway_subscription_ref')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['subscriber_type', 'subscriber_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('laravel-subscriptions.tables.subscriptions'));
    }
};
