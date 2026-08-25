<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEventRequest;
use App\Http\Requests\Admin\UpdateEventRequest;
use App\Models\Event;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Event::class);

        return Inertia::render('admin/events/Index', [
            'events' => Event::query()
                ->orderByDesc('start_at')
                ->paginate(20)
                ->through(fn (Event $event) => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'slug' => $event->slug,
                    'start_at' => $event->start_at->toIso8601String(),
                    'location' => $event->location,
                    'status' => $event->status->value,
                    'status_label' => $event->status->label(),
                ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Event::class);

        return Inertia::render('admin/events/Create', [
            'statusOptions' => ContentStatus::options(),
        ]);
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $event = Event::create($data);

        $this->auditLogger->log($request->user(), 'create', $event, newValues: $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$event->title}\" was created.",
        ]);

        return to_route('admin.events.index');
    }

    public function edit(Event $event): Response
    {
        $this->authorize('update', $event);

        return Inertia::render('admin/events/Edit', [
            'event' => [
                ...$event->only(['id', 'title', 'slug', 'description', 'location', 'featured_image_media_id', 'status']),
                'start_at' => $event->start_at->format('Y-m-d\TH:i'),
                'end_at' => $event->end_at?->format('Y-m-d\TH:i'),
                'featured_image_thumbnail_url' => $event->featuredImage?->thumbnailUrl(),
            ],
            'statusOptions' => ContentStatus::options(),
        ]);
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $oldValues = $event->only(array_keys($request->validated()));
        $data = $request->validated();

        $event->update($data);

        $this->auditLogger->log($request->user(), 'update', $event, $oldValues, $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$event->title}\" was updated.",
        ]);

        return to_route('admin.events.index');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        $title = $event->title;
        $this->auditLogger->log(request()->user(), 'delete', $event, oldValues: $event->only(['title', 'slug', 'status']));
        $event->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$title}\" was deleted.",
        ]);

        return to_route('admin.events.index');
    }
}
