<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supporter_id')->constrained('supporters')->restrictOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();

            // Integer cents, not decimal dollars — matches Stripe's own
            // amount representation and avoids float-rounding drift.
            // See docs/architecture/payment-architecture.md §8.
            $table->unsignedInteger('amount_cents');
            $table->char('currency', 3)->default('USD');
            $table->string('frequency');
            $table->boolean('anonymous')->default(false);
            $table->string('status')->default('pending');

            // Needed to resolve `invoice.paid` / `customer.subscription.deleted`
            // webhook events back to the recurring Donation they belong to.
            $table->string('stripe_subscription_id')->nullable()->unique();

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
