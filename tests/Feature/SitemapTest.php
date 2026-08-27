<?php

use App\Enums\ContentStatus;
use App\Models\Document;
use App\Models\Event;
use App\Models\Page;
use App\Models\Program;
use App\Models\Story;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::forget('sitemap.xml');
});

test('the sitemap includes static routes', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/xml');
    expect($response->getContent())
        ->toContain(route('home'))
        ->toContain(route('give.show'))
        ->toContain(route('programs.index'));
});

test('the sitemap includes published content and excludes drafts', function () {
    $program = Program::factory()->create(['status' => ContentStatus::Published]);
    Program::factory()->create(['status' => ContentStatus::Draft, 'slug' => 'draft-program']);

    $story = Story::factory()->published()->create();
    $page = Page::factory()->published()->create();
    $document = Document::factory()->create(['status' => ContentStatus::Published, 'published_at' => now()]);
    $event = Event::factory()->create(['status' => ContentStatus::Published]);

    $response = $this->get('/sitemap.xml');
    $content = $response->getContent();

    expect($content)
        ->toContain(route('programs.show', $program->slug))
        ->toContain(route('stories.show', $story->slug))
        ->toContain(route('pages.show', $page->slug))
        ->toContain(route('documents.show', $document->id))
        ->toContain(route('events.show', $event->slug))
        ->not->toContain('draft-program');
});

test('the sitemap is valid xml', function () {
    Story::factory()->published()->create();

    $response = $this->get('/sitemap.xml');

    $xml = simplexml_load_string($response->getContent());

    expect($xml)->not->toBeFalse();
    expect((string) $xml->getName())->toBe('urlset');
});

test('the sitemap is cached for a day', function () {
    Story::factory()->published()->create(['title' => 'Cached Before']);

    $this->get('/sitemap.xml');

    Story::factory()->published()->create(['title' => 'Cached After']);
    $second = $this->get('/sitemap.xml');

    expect(Cache::has('sitemap.xml'))->toBeTrue();
    $secondStory = Story::query()->where('title', 'Cached After')->first();
    expect($second->getContent())->not->toContain(route('stories.show', $secondStory->slug));
});
