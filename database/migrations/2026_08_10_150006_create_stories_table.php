<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->foreignId('featured_image_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->string('story_type');
            $table->string('location')->nullable();

            // Governance fields — required whenever the story depicts an
            // identifiable person. See docs/content-model.md §2.4.
            $table->string('consent_status')->nullable();
            $table->string('image_consent')->nullable();
            $table->string('guardian_consent')->nullable();
            $table->boolean('anonymity_requested')->default(false);
            $table->string('sensitive_content_classification')->default('none');
            $table->string('approval_stage')->default('draft');
            $table->string('attribution')->nullable();

            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index('story_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
