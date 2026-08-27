<?php

namespace App\Http\Controllers;

use App\Enums\ContentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\TeamMember;
use Inertia\Inertia;
use Inertia\Response;

class AboutPageController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('About', [
            // Sammy Tongoi (Draft, no bio — docs/audit/current-website-audit.md
            // F-9) is excluded by this query, not hidden by the frontend —
            // an unpublished team member never reaches the page at all.
            'teamMembers' => TeamMember::query()
                ->where('status', ContentStatus::Published)
                ->orderBy('display_order')
                ->get(['name', 'role', 'bio', 'board_member']),
            // Governance/Financial Transparency render from whatever's
            // actually been published in the Documents CMS (saba.md §3.3's
            // content sustainability mandate) rather than a hardcoded
            // "content required" notice that would stay wrong forever even
            // after an editor uploads real policies or reports - the page
            // falls back to that notice only when these are empty.
            'governanceDocuments' => Document::query()
                ->where('status', ContentStatus::Published)
                ->where('document_type', DocumentType::Policy)
                ->orderBy('title')
                ->get(['id', 'title', 'summary'])
                ->map(fn (Document $document) => [
                    'id' => $document->id,
                    'title' => $document->title,
                    'summary' => $document->summary,
                ]),
            'financialDocuments' => Document::query()
                ->where('status', ContentStatus::Published)
                ->whereIn('document_type', [DocumentType::AnnualReport, DocumentType::FinancialReport])
                ->orderByDesc('year')
                ->get(['id', 'title', 'document_type', 'year', 'summary'])
                ->map(fn (Document $document) => [
                    'id' => $document->id,
                    'title' => $document->title,
                    'document_type_label' => $document->document_type->label(),
                    'year' => $document->year,
                    'summary' => $document->summary,
                ]),
        ]);
    }
}
