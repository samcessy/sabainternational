<?php

namespace Database\Factories;

use App\Enums\MediaVariantType;
use App\Models\Media;
use App\Models\MediaVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaVariant>
 */
class MediaVariantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'media_id' => Media::factory(),
            'variant_type' => MediaVariantType::Medium,
            'path' => 'media/variants/'.fake()->uuid().'.webp',
            'width' => 800,
            'height' => 600,
        ];
    }
}
