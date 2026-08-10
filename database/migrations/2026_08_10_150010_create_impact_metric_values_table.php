<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impact_metric_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('metric_id')->constrained('impact_metrics')->cascadeOnDelete();
            $table->decimal('value', 12, 2);
            $table->string('time_period');
            $table->string('data_source')->nullable();
            $table->string('verification_status')->default('unverified');
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();

            $table->index(['metric_id', 'verification_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impact_metric_values');
    }
};
