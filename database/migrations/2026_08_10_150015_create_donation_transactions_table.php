<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donation_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donation_id')->constrained('donations')->cascadeOnDelete();
            $table->string('gateway')->default('stripe');

            // Unique for webhook idempotency — a retried Stripe webhook for
            // an event we've already recorded is a no-op, not a duplicate
            // transaction. See docs/architecture/payment-architecture.md §5.
            $table->string('gateway_reference')->unique();

            $table->string('status')->default('pending');
            $table->timestamp('receipt_sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donation_transactions');
    }
};
