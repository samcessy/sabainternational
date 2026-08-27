<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStoryTagRequest;
use App\Http\Requests\Admin\UpdateStoryTagRequest;
use App\Models\StoryTag;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class StoryTagController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): Response
    {
        $this->authorize('viewAny', StoryTag::class);

        return Inertia::render('admin/story-tags/Index', [
            'storyTags' => StoryTag::query()
                ->withCount('stories')
                ->orderBy('name')
                ->paginate(20)
                ->through(fn (StoryTag $storyTag) => [
                    'id' => $storyTag->id,
                    'name' => $storyTag->name,
                    'slug' => $storyTag->slug,
                    'stories_count' => $storyTag->stories_count,
                ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', StoryTag::class);

        return Inertia::render('admin/story-tags/Create');
    }

    public function store(StoreStoryTagRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $storyTag = StoryTag::create($data);

        $this->auditLogger->log($request->user(), 'create', $storyTag, newValues: $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$storyTag->name}\" was created.",
        ]);

        return to_route('admin.story-tags.index');
    }

    public function edit(StoryTag $storyTag): Response
    {
        $this->authorize('update', $storyTag);

        return Inertia::render('admin/story-tags/Edit', [
            'storyTag' => $storyTag->only(['id', 'name', 'slug']),
        ]);
    }

    public function update(UpdateStoryTagRequest $request, StoryTag $storyTag): RedirectResponse
    {
        $oldValues = $storyTag->only(array_keys($request->validated()));
        $data = $request->validated();

        $storyTag->update($data);

        $this->auditLogger->log($request->user(), 'update', $storyTag, $oldValues, $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$storyTag->name}\" was updated.",
        ]);

        return to_route('admin.story-tags.index');
    }

    public function destroy(StoryTag $storyTag): RedirectResponse
    {
        $this->authorize('delete', $storyTag);

        $name = $storyTag->name;
        $this->auditLogger->log(request()->user(), 'delete', $storyTag, oldValues: $storyTag->only(['name', 'slug']));
        $storyTag->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$name}\" was deleted.",
        ]);

        return to_route('admin.story-tags.index');
    }
}
