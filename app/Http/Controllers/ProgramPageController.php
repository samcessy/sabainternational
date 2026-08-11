<?php

namespace App\Http\Controllers;

use App\Enums\ContentStatus;
use App\Http\Resources\ProgramResource;
use App\Models\Program;
use Inertia\Inertia;
use Inertia\Response;

class ProgramPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('programs/Index', [
            'programs' => Program::query()
                ->where('status', ContentStatus::Published)
                ->orderBy('name')
                ->get(['name', 'slug', 'category', 'short_description']),
        ]);
    }

    public function show(string $slug): Response
    {
        $program = Program::query()
            ->where('status', ContentStatus::Published)
            ->where('slug', $slug)
            ->firstOrFail();

        return Inertia::render('programs/Show', [
            // ->resolve(), not the bare Resource — Inertia's response
            // pipeline applies Laravel's `data`-wrapping to an embedded
            // Resource object, which is meant for HTTP API responses, not
            // Inertia props. Resolving here keeps the prop shape flat.
            'program' => (new ProgramResource($program))->resolve(),
        ]);
    }
}
