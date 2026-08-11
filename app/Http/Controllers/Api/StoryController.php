<?php

namespace App\Http\Controllers\Api;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\StoryResource;
use App\Models\Story;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return StoryResource::collection(
            Story::query()
                ->where('status', ContentStatus::Published)
                ->with('program')
                ->latest('published_at')
                ->paginate(20)
        );
    }

    public function show(string $slug): StoryResource
    {
        $story = Story::query()
            ->where('status', ContentStatus::Published)
            ->where('slug', $slug)
            ->with('program')
            ->firstOrFail();

        return new StoryResource($story);
    }
}
