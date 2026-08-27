<?php

use App\Enums\ContentStatus;
use App\Models\Page;
use Inertia\Testing\AssertableInertia as Assert;

test('a published page show page renders by slug', function () {
    Page::factory()->published()->create(['slug' => 'our-mission', 'title' => 'Our Mission']);

    $response = $this->get(route('pages.show', 'our-mission'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('pages/Show')
        ->where('page.slug', 'our-mission')
        ->where('page.title', 'Our Mission')
    );
});

test('a draft page returns 404 on its show page', function () {
    Page::factory()->create(['slug' => 'draft-page', 'status' => ContentStatus::Draft]);

    $this->get(route('pages.show', 'draft-page'))->assertNotFound();
});

test('a nonexistent page slug returns 404', function () {
    $this->get(route('pages.show', 'does-not-exist'))->assertNotFound();
});
