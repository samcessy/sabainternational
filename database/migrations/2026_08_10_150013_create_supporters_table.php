<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supporters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            // One reusable Stripe Customer per Supporter — see
            // docs/architecture/payment-architecture.md §3.2, §8.
            $table->string('stripe_customer_id')->nullable()->unique();
            $table->json('communication_preferences')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supporters');
    }
};
