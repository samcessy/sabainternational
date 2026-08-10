<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('document_type');
            $table->unsignedSmallInteger('year')->nullable();
            $table->text('summary')->nullable();
            $table->foreignId('file_media_id')->constrained('media')->restrictOnDelete();
            $table->foreignId('cover_image_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('document_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
