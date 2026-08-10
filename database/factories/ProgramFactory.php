<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Enums\ProgramCategory;
use App\Enums\ProgramRelationshipType;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 100000),
            'category' => fake()->randomElement(ProgramCategory::cases()),
            'relationship_type' => ProgramRelationshipType::Unconfirmed,
            'founded_year' => fake()->numberBetween(1990, 2024),
            'location' => fake()->city(),
            'short_description' => fake()->sentence(),
            'overview' => fake()->paragraphs(3, true),
            'status' => ContentStatus::Draft,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);
    }
}
