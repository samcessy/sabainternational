<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('story_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('story_tag', function (Blueprint $table) {
            $table->foreignId('story_id')->constrained('stories')->cascadeOnDelete();
            $table->foreignId('story_tag_id')->constrained('story_tags')->cascadeOnDelete();

            $table->primary(['story_id', 'story_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('story_tag');
        Schema::dropIfExists('story_tags');
    }
};
