<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApprovalStage;
use App\Enums\ConsentStatus;
use App\Enums\ContentStatus;
use App\Enums\ImageConsentStatus;
use App\Enums\SensitiveContentClassification;
use App\Enums\StoryType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStoryRequest;
use App\Http\Requests\Admin\UpdateStoryRequest;
use App\Models\Program;
use App\Models\Story;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class StoryController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Story::class);

        return Inertia::render('admin/stories/Index', [
            'stories' => Story::query()
                ->with('program')
                ->orderByDesc('updated_at')
                ->paginate(20)
                ->through(fn (Story $story) => [
                    'id' => $story->id,
                    'title' => $story->title,
                    'slug' => $story->slug,
                    'story_type_label' => $story->story_type->label(),
                    'status' => $story->status->value,
                    'status_label' => $story->status->label(),
                    'consent_status_label' => $story->consent_status?->label(),
                    'program' => $story->program?->name,
                    'updated_at' => $story->updated_at?->toIso8601String(),
                ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Story::class);

        return Inertia::render('admin/stories/Create', $this->formOptions());
    }

    public function store(StoreStoryRequest $request): RedirectResponse
    {
        $data = $this->normalizeBooleans($request);
        $data['author_id'] = $request->user()->id;

        if ($data['status'] === ContentStatus::Published->value) {
            $data['published_at'] = now();
        }

        $story = Story::create($data);

        $this->auditLogger->log($request->user(), 'create', $story, newValues: $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$story->title}\" was created.",
        ]);

        return to_route('admin.stories.index');
    }

    public function edit(Story $story): Response
    {
        $this->authorize('update', $story);

        return Inertia::render('admin/stories/Edit', [
            'story' => [
                ...$story->only([
                    'id', 'title', 'slug', 'excerpt', 'body', 'featured_image_media_id', 'program_id',
                    'story_type', 'location', 'consent_status', 'image_consent', 'guardian_consent',
                    'anonymity_requested', 'sensitive_content_classification', 'approval_stage',
                    'attribution', 'seo_title', 'seo_description', 'og_image', 'status', 'featured',
                ]),
                'featured_image_thumbnail_url' => $story->featuredImage?->thumbnailUrl(),
            ],
            ...$this->formOptions(),
        ]);
    }

    public function update(UpdateStoryRequest $request, Story $story): RedirectResponse
    {
        $oldValues = $story->only(array_keys($request->validated()));
        $data = $this->normalizeBooleans($request);

        if ($data['status'] === ContentStatus::Published->value && $story->published_at === null) {
            $data['published_at'] = now();
        }

        $story->update($data);

        $this->auditLogger->log($request->user(), 'update', $story, $oldValues, $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$story->title}\" was updated.",
        ]);

        return to_route('admin.stories.index');
    }

    public function destroy(Story $story): RedirectResponse
    {
        $this->authorize('delete', $story);

        $title = $story->title;
        $this->auditLogger->log(request()->user(), 'delete', $story, oldValues: $story->only(['title', 'slug', 'status']));
        $story->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$title}\" was deleted.",
        ]);

        return to_route('admin.stories.index');
    }

    /**
     * FormData omits unchecked checkboxes entirely rather than sending
     * false, so ->validated() can't be trusted for these two fields -
     * ->boolean() correctly treats "absent" as false either way.
     *
     * @return array<string, mixed>
     */
    private function normalizeBooleans(StoreStoryRequest|UpdateStoryRequest $request): array
    {
        return [
            ...$request->validated(),
            'featured' => $request->boolean('featured'),
            'anonymity_requested' => $request->boolean('anonymity_requested'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'programOptions' => Program::query()->orderBy('name')->get(['id', 'name']),
            'storyTypeOptions' => StoryType::options(),
            'consentStatusOptions' => ConsentStatus::options(),
            'imageConsentOptions' => ImageConsentStatus::options(),
            'sensitiveContentOptions' => SensitiveContentClassification::options(),
            'approvalStageOptions' => ApprovalStage::options(),
            'statusOptions' => ContentStatus::options(),
        ];
    }
}
