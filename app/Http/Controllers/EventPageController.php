<?php

namespace App\Http\Controllers;

use App\Enums\ContentStatus;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Inertia\Inertia;
use Inertia\Response;

class EventPageController extends Controller
{
    public function index(): Response
    {
        $published = Event::query()->where('status', ContentStatus::Published);

        return Inertia::render('events/Index', [
            'upcoming' => (clone $published)
                ->where('start_at', '>=', now())
                ->orderBy('start_at')
                ->get()
                ->map(fn (Event $event) => (new EventResource($event))->resolve()),
            'past' => (clone $published)
                ->where('start_at', '<', now())
                ->orderByDesc('start_at')
                ->get()
                ->map(fn (Event $event) => (new EventResource($event))->resolve()),
        ]);
    }

    public function show(string $slug): Response
    {
        $event = Event::query()
            ->where('status', ContentStatus::Published)
            ->where('slug', $slug)
            ->firstOrFail();

        return Inertia::render('events/Show', [
            'event' => (new EventResource($event))->resolve(),
        ]);
    }
}
