<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'document_type' => DocumentType::AnnualReport,
            'year' => fake()->numberBetween(2020, 2026),
            'summary' => fake()->paragraph(),
            'file_media_id' => Media::factory(),
            'status' => ContentStatus::Draft,
        ];
    }
}
