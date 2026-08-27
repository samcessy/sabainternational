<?php

use App\Enums\ContentStatus;
use App\Models\Event;
use Inertia\Testing\AssertableInertia as Assert;

test('the events index splits published events into upcoming and past', function () {
    Event::factory()->create([
        'title' => 'Future Gala',
        'status' => ContentStatus::Published,
        'start_at' => now()->addWeek(),
    ]);
    Event::factory()->create([
        'title' => 'Past Fundraiser',
        'status' => ContentStatus::Published,
        'start_at' => now()->subWeek(),
    ]);
    Event::factory()->create(['title' => 'Draft Event', 'status' => ContentStatus::Draft]);

    $response = $this->get(route('events.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('events/Index')
        ->has('upcoming', 1)
        ->where('upcoming.0.title', 'Future Gala')
        ->has('past', 1)
        ->where('past.0.title', 'Past Fundraiser')
    );
});

test('a published event show page renders by slug', function () {
    Event::factory()->create(['slug' => 'annual-gala', 'status' => ContentStatus::Published]);

    $response = $this->get(route('events.show', 'annual-gala'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('events/Show')
        ->where('event.slug', 'annual-gala')
    );
});

test('a draft event returns 404 on its show page', function () {
    Event::factory()->create(['slug' => 'draft-event', 'status' => ContentStatus::Draft]);

    $this->get(route('events.show', 'draft-event'))->assertNotFound();
});

test('a nonexistent event slug returns 404', function () {
    $this->get(route('events.show', 'does-not-exist'))->assertNotFound();
});
