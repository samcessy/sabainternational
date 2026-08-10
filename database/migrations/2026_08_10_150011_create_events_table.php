<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamp('start_at');
            $table->timestamp('end_at')->nullable();
            $table->string('location')->nullable();
            $table->foreignId('featured_image_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->index('start_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
