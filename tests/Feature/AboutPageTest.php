<?php

use App\Enums\ContentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
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

test('the about page has no governance or financial documents when none are published', function () {
    $response = $this->get(route('about.show'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('About')
        ->has('governanceDocuments', 0)
        ->has('financialDocuments', 0)
    );
});

test('published policy documents appear as governance documents', function () {
    Document::factory()->create([
        'title' => 'Child Protection Policy',
        'document_type' => DocumentType::Policy,
        'status' => ContentStatus::Published,
        'published_at' => now(),
    ]);
    Document::factory()->create([
        'title' => 'Draft Policy',
        'document_type' => DocumentType::Policy,
        'status' => ContentStatus::Draft,
    ]);
    Document::factory()->create([
        'title' => '2025 Annual Report',
        'document_type' => DocumentType::AnnualReport,
        'status' => ContentStatus::Published,
        'published_at' => now(),
    ]);

    $response = $this->get(route('about.show'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('About')
        ->has('governanceDocuments', 1)
        ->where('governanceDocuments.0.title', 'Child Protection Policy')
    );
});

test('published annual and financial reports appear as financial documents, newest year first', function () {
    Document::factory()->create([
        'title' => '2023 Annual Report',
        'document_type' => DocumentType::AnnualReport,
        'year' => 2023,
        'status' => ContentStatus::Published,
        'published_at' => now(),
    ]);
    Document::factory()->create([
        'title' => '2025 Financial Statement',
        'document_type' => DocumentType::FinancialReport,
        'year' => 2025,
        'status' => ContentStatus::Published,
        'published_at' => now(),
    ]);
    Document::factory()->create([
        'title' => 'Code of Conduct',
        'document_type' => DocumentType::Policy,
        'status' => ContentStatus::Published,
        'published_at' => now(),
    ]);

    $response = $this->get(route('about.show'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('About')
        ->has('financialDocuments', 2)
        ->where('financialDocuments.0.title', '2025 Financial Statement')
        ->where('financialDocuments.1.title', '2023 Annual Report')
    );
});
