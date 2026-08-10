<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('slug')->unique();
            $table->string('category');
            $table->string('relationship_type')->default('unconfirmed');
            $table->string('external_url')->nullable();
            $table->unsignedSmallInteger('founded_year')->nullable();
            $table->string('location')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('overview')->nullable();
            $table->longText('what_happens_here')->nullable();
            $table->string('sensitive_content_classification')->default('none');
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('category');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
