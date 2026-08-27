<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePageRequest;
use App\Http\Requests\Admin\UpdatePageRequest;
use App\Models\Page;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Page::class);

        return Inertia::render('admin/pages/Index', [
            'pages' => Page::query()
                ->orderBy('title')
                ->paginate(20)
                ->through(fn (Page $page) => [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'status' => $page->status->value,
                    'status_label' => $page->status->label(),
                    'updated_at' => $page->updated_at?->toIso8601String(),
                ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Page::class);

        return Inertia::render('admin/pages/Create', [
            'statusOptions' => ContentStatus::options(),
        ]);
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['author_id'] = $request->user()->id;

        if ($data['status'] === ContentStatus::Published->value) {
            $data['published_at'] = now();
        }

        $page = Page::create($data);

        $this->auditLogger->log($request->user(), 'create', $page, newValues: $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$page->title}\" was created.",
        ]);

        return to_route('admin.pages.index');
    }

    public function edit(Page $page): Response
    {
        $this->authorize('update', $page);

        return Inertia::render('admin/pages/Edit', [
            'page' => $page->only(['id', 'title', 'slug', 'body', 'seo_title', 'seo_description', 'og_image', 'status']),
            'statusOptions' => ContentStatus::options(),
        ]);
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $oldValues = $page->only(array_keys($request->validated()));
        $data = $request->validated();

        if ($data['status'] === ContentStatus::Published->value && $page->published_at === null) {
            $data['published_at'] = now();
        }

        $page->update($data);

        $this->auditLogger->log($request->user(), 'update', $page, $oldValues, $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$page->title}\" was updated.",
        ]);

        return to_route('admin.pages.index');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $this->authorize('delete', $page);

        $title = $page->title;
        $this->auditLogger->log(request()->user(), 'delete', $page, oldValues: $page->only(['title', 'slug', 'status']));
        $page->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$title}\" was deleted.",
        ]);

        return to_route('admin.pages.index');
    }
}
