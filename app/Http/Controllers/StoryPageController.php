<?php

namespace App\Http\Controllers;

use App\Enums\ContentStatus;
use App\Http\Resources\StoryResource;
use App\Models\Story;
use Inertia\Inertia;
use Inertia\Response;

class StoryPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('stories/Index', [
            'stories' => Story::query()
                ->where('status', ContentStatus::Published)
                ->with('program')
                ->orderByDesc('published_at')
                ->paginate(12)
                ->through(fn (Story $story) => [
                    'title' => $story->title,
                    'slug' => $story->slug,
                    'excerpt' => $story->excerpt,
                    'story_type' => $story->story_type->value,
                    'featured' => $story->featured,
                    'program' => $story->program ? [
                        'name' => $story->program->name,
                        'slug' => $story->program->slug,
                    ] : null,
                    'published_at' => $story->published_at?->toIso8601String(),
                ]),
        ]);
    }

    public function show(string $slug): Response
    {
        $story = Story::query()
            ->where('status', ContentStatus::Published)
            ->where('slug', $slug)
            ->with('program')
            ->firstOrFail();

        return Inertia::render('stories/Show', [
            // ->resolve(), not the bare Resource — see ProgramPageController.
            'story' => (new StoryResource($story))->resolve(),
        ]);
    }
}
