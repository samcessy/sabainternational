<?php

use App\Enums\ContentStatus;
use App\Models\Story;
use App\Models\StoryTag;
use Inertia\Testing\AssertableInertia as Assert;

test('the stories index renders only published stories', function () {
    Story::factory()->published()->create(['title' => 'Zebra Story']);
    Story::factory()->published()->create(['title' => 'Alpha Story']);
    Story::factory()->create(['title' => 'Hidden Draft', 'status' => ContentStatus::Draft]);

    $response = $this->get(route('stories.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('stories/Index')
        ->has('stories.data', 2)
        ->where('stories.data.0.title', 'Alpha Story')
    );
});

test('a published story show page renders by slug', function () {
    Story::factory()->published()->create(['slug' => 'a-story-of-change']);

    $response = $this->get(route('stories.show', 'a-story-of-change'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('stories/Show')
        ->where('story.slug', 'a-story-of-change')
    );
});

test('a draft story returns 404 on its show page', function () {
    Story::factory()->create(['slug' => 'draft-story', 'status' => ContentStatus::Draft]);

    $this->get(route('stories.show', 'draft-story'))->assertNotFound();
});

test('a nonexistent story slug returns 404', function () {
    $this->get(route('stories.show', 'does-not-exist'))->assertNotFound();
});

test('a published story show page includes its tags', function () {
    $tag = StoryTag::factory()->create(['name' => 'Education']);
    $story = Story::factory()->published()->create(['slug' => 'a-story-of-change']);
    $story->tags()->attach($tag);

    $response = $this->get(route('stories.show', 'a-story-of-change'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('stories/Show')
        ->has('story.tags', 1)
        ->where('story.tags.0.name', 'Education')
    );
});
