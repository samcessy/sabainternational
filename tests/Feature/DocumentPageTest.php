<?php

use App\Enums\ContentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use Inertia\Testing\AssertableInertia as Assert;

test('the documents index renders only published documents', function () {
    Document::factory()->create([
        'title' => '2025 Annual Report',
        'document_type' => DocumentType::AnnualReport,
        'status' => ContentStatus::Published,
        'published_at' => now(),
    ]);
    Document::factory()->create(['title' => 'Draft Report', 'status' => ContentStatus::Draft]);

    $response = $this->get(route('documents.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('documents/Index')
        ->has('documents', 1)
        ->where('documents.0.title', '2025 Annual Report')
    );
});

test('a published document show page renders with a download link', function () {
    $document = Document::factory()->create([
        'title' => 'Financial Statement',
        'status' => ContentStatus::Published,
        'published_at' => now(),
    ]);

    $response = $this->get(route('documents.show', $document));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('documents/Show')
        ->where('document.title', 'Financial Statement')
        ->has('document.file_url')
    );
});

test('a draft document returns 404 on its show page', function () {
    $document = Document::factory()->create(['status' => ContentStatus::Draft]);

    $this->get(route('documents.show', $document))->assertNotFound();
});

test('a nonexistent document id returns 404', function () {
    $this->get('/documents/999999')->assertNotFound();
});
