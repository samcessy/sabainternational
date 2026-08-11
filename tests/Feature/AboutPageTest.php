<?php

use App\Enums\ContentStatus;
use App\Models\TeamMember;
use Inertia\Testing\AssertableInertia as Assert;

test('the about page renders with published team members only, in display order', function () {
    TeamMember::factory()->create(['name' => 'Second', 'display_order' => 2, 'status' => ContentStatus::Published]);
    TeamMember::factory()->create(['name' => 'First', 'display_order' => 1, 'status' => ContentStatus::Published]);
    TeamMember::factory()->create(['name' => 'Draft Member', 'status' => ContentStatus::Draft]);

    $response = $this->get(route('about.show'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('About')
        ->has('teamMembers', 2)
        ->where('teamMembers.0.name', 'First')
        ->where('teamMembers.1.name', 'Second')
    );
});
