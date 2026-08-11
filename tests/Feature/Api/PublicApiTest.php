<?php

use App\Enums\ContentStatus;
use App\Enums\ProgramCategory;
use App\Models\Campaign;
use App\Models\Page;
use App\Models\Program;
use App\Models\Story;
use App\Models\TeamMember;

test('pages api only returns published pages', function () {
    Page::factory()->published()->create(['title' => 'Published Page']);
    Page::factory()->create(['title' => 'Draft Page', 'status' => ContentStatus::Draft]);

    $response = $this->getJson('/api/v1/pages');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Published Page')
        ->assertJsonStructure(['data', 'links', 'meta']);
});

test('pages api show returns 404 for a draft page', function () {
    $page = Page::factory()->create(['slug' => 'draft-page', 'status' => ContentStatus::Draft]);

    $this->getJson("/api/v1/pages/{$page->slug}")->assertNotFound();
});

test('pages api show returns a published page by slug', function () {
    $page = Page::factory()->published()->create(['slug' => 'about-us']);

    $this->getJson('/api/v1/pages/about-us')
        ->assertOk()
        ->assertJsonPath('data.slug', 'about-us');
});

test('programs api only returns published programs', function () {
    Program::factory()->published()->create();
    Program::factory()->create(['status' => ContentStatus::Draft]);

    $this->getJson('/api/v1/programs')->assertOk()->assertJsonCount(1, 'data');
});

test('programs api exposes category as a value and label', function () {
    $program = Program::factory()->published()->create([
        'category' => ProgramCategory::Education,
    ]);

    $this->getJson("/api/v1/programs/{$program->slug}")
        ->assertOk()
        ->assertJsonPath('data.category', 'education')
        ->assertJsonPath('data.category_label', 'Education');
});

test('stories api only returns published stories and includes program when loaded', function () {
    $program = Program::factory()->published()->create();
    Story::factory()->requiresConsent()->published()->create(['program_id' => $program->id]);
    Story::factory()->create(['status' => ContentStatus::Draft]);

    $response = $this->getJson('/api/v1/stories');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.program.slug', $program->slug);
});

test('stories api does not leak internal governance fields', function () {
    $story = Story::factory()->requiresConsent()->published()->create();

    $response = $this->getJson("/api/v1/stories/{$story->slug}");

    $response->assertOk()
        ->assertJsonMissingPath('data.consent_status')
        ->assertJsonMissingPath('data.approval_stage')
        ->assertJsonMissingPath('data.sensitive_content_classification');
});

test('team api only returns published team members ordered by display order', function () {
    TeamMember::factory()->published()->create(['name' => 'Second', 'display_order' => 2]);
    TeamMember::factory()->published()->create(['name' => 'First', 'display_order' => 1]);
    TeamMember::factory()->create(['name' => 'Draft Member', 'status' => ContentStatus::Draft]);

    $response = $this->getJson('/api/v1/team');

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'First')
        ->assertJsonPath('data.1.name', 'Second');
});

test('campaigns api only returns published campaigns', function () {
    Campaign::factory()->create(['status' => ContentStatus::Published]);
    Campaign::factory()->create(['status' => ContentStatus::Draft]);

    $this->getJson('/api/v1/campaigns')->assertOk()->assertJsonCount(1, 'data');
});

test('public api responses are rate limited', function () {
    $response = $this->getJson('/api/v1/pages');

    $response->assertHeader('X-RateLimit-Limit', 60);
});

test('robots.txt disallows the api and admin surfaces', function () {
    // Served as a static file by the web server, not through Laravel's
    // router — read it directly rather than dispatching an HTTP request.
    $contents = file_get_contents(public_path('robots.txt'));

    expect($contents)
        ->toContain('Disallow: /api/')
        ->toContain('Disallow: /dashboard');
});
