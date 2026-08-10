<?php

namespace Database\Factories;

use App\Enums\ApprovalStage;
use App\Enums\ConsentStatus;
use App\Enums\ContentStatus;
use App\Enums\StoryType;
use App\Models\Story;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Story>
 */
class StoryFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(6);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 100000),
            'excerpt' => fake()->sentence(),
            'body' => fake()->paragraphs(4, true),
            'story_type' => StoryType::News,
            'consent_status' => ConsentStatus::NotRequired,
            'approval_stage' => ApprovalStage::Draft,
            'status' => ContentStatus::Draft,
            'featured' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => ContentStatus::Published,
            'approval_stage' => ApprovalStage::Published,
            'published_at' => now(),
        ]);
    }

    public function requiresConsent(): static
    {
        return $this->state(fn () => ['consent_status' => ConsentStatus::Yes]);
    }
}
