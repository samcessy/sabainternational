<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamMember>
 */
class TeamMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'role' => fake()->jobTitle(),
            'bio' => fake()->paragraph(),
            'board_member' => fake()->boolean(30),
            'consent_to_publish' => true,
            'display_order' => fake()->numberBetween(0, 10),
            'status' => ContentStatus::Draft,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => ContentStatus::Published]);
    }
}
