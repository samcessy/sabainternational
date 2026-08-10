<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('path');
            $table->string('alt_text')->nullable();
            $table->string('caption')->nullable();
            $table->string('photographer')->nullable();
            $table->string('copyright_license')->nullable();
            $table->string('consent_status')->nullable();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();

            // story_id intentionally has no FK constraint here: stories
            // references media (featured_image_media_id) and media
            // references stories (gallery membership), a genuine circular
            // dependency. Kept as a plain indexed column rather than
            // resolved with a deferred/alter-table FK for one relationship.
            $table->unsignedBigInteger('story_id')->nullable();

            $table->decimal('focal_point_x', 4, 3)->default(0.5);
            $table->decimal('focal_point_y', 4, 3)->default(0.5);
            $table->json('exif_data')->nullable();
            $table->timestamps();

            $table->index('story_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
