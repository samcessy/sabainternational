<?php

namespace Database\Factories;

use App\Enums\ImageConsentStatus;
use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    public function definition(): array
    {
        $filename = fake()->uuid().'.jpg';

        return [
            'filename' => $filename,
            'path' => 'media/'.$filename,
            'alt_text' => fake()->sentence(),
            'caption' => fake()->optional()->sentence(),
            'photographer' => fake()->optional()->name(),
            'consent_status' => ImageConsentStatus::Yes,
        ];
    }
}
