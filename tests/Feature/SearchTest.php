<?php

use App\Enums\ContentStatus;
use App\Models\Document;
use App\Models\Event;
use App\Models\Page;
use App\Models\Program;
use App\Models\Story;
use Inertia\Testing\AssertableInertia as Assert;

test('a blank query renders the search page with no results', function () {
    $response = $this->get('/search');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Search')
        ->where('query', '')
        ->where('results', [])
    );
});

test('search finds matches across every published content type', function () {
    Story::factory()->published()->create(['title' => 'Nairobi Story', 'excerpt' => null]);
    Program::factory()->create(['name' => 'Nairobi Program', 'status' => ContentStatus::Published]);
    Document::factory()->create(['title' => 'Nairobi Report', 'status' => ContentStatus::Published, 'published_at' => now()]);
    Page::factory()->published()->create(['title' => 'Nairobi Page']);
    Event::factory()->create(['title' => 'Nairobi Gala', 'status' => ContentStatus::Published]);

    $response = $this->get('/search?q=Nairobi');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Search')
        ->where('query', 'Nairobi')
        ->has('results', 5)
    );
});

test('search excludes unpublished content', function () {
    Story::factory()->create(['title' => 'Draft Nairobi Story', 'status' => ContentStatus::Draft]);

    $response = $this->get('/search?q=Nairobi');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Search')
        ->has('results', 0)
    );
});

test('an exact title match ranks before a partial match', function () {
    Story::factory()->published()->create(['title' => 'Water', 'excerpt' => null]);
    Story::factory()->published()->create(['title' => 'Clean Water Access', 'excerpt' => null]);

    $response = $this->get('/search?q=Water&type=story');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Search')
        ->where('results.0.title', 'Water')
        ->where('results.1.title', 'Clean Water Access')
    );
});

test('a type filter restricts results to that content type', function () {
    Story::factory()->published()->create(['title' => 'Nairobi Story', 'excerpt' => null]);
    Program::factory()->create(['name' => 'Nairobi Program', 'status' => ContentStatus::Published]);

    $response = $this->get('/search?q=Nairobi&type=program');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Search')
        ->where('type', 'program')
        ->has('results', 1)
        ->where('results.0.type', 'program')
    );
});

test('a type filter paginates results', function () {
    Story::factory()->published()->count(25)->create(['title' => 'Nairobi Story', 'excerpt' => null]);

    $response = $this->get('/search?q=Nairobi&type=story');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Search')
        ->has('results', 20)
        ->where('pagination.total', 25)
        ->where('pagination.last_page', 2)
    );

    $secondPage = $this->get('/search?q=Nairobi&type=story&page=2');

    $secondPage->assertInertia(fn (Assert $page) => $page
        ->has('results', 5)
        ->where('pagination.current_page', 2)
    );
});

test('a search with no matches shows an empty result set', function () {
    $response = $this->get('/search?q=zzzznotfound');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Search')
        ->where('results', [])
    );
});
