<?php

namespace App\Http\Controllers;

use App\Enums\ContentStatus;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use Inertia\Inertia;
use Inertia\Response;

/**
 * saba.md §9.2 — the Transparency Center's annual report / financial
 * document archive.
 */
class DocumentPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('documents/Index', [
            'documents' => Document::query()
                ->with(['file', 'coverImage'])
                ->where('status', ContentStatus::Published)
                ->orderByDesc('year')
                ->orderByDesc('published_at')
                ->get()
                ->map(fn (Document $document) => (new DocumentResource($document))->resolve()),
        ]);
    }

    public function show(int $document): Response
    {
        $document = Document::query()
            ->with(['file', 'coverImage'])
            ->where('status', ContentStatus::Published)
            ->where('id', $document)
            ->firstOrFail();

        return Inertia::render('documents/Show', [
            'document' => (new DocumentResource($document))->resolve(),
        ]);
    }
}
