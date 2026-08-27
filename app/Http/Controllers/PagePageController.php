<?php

namespace App\Http\Controllers;

use App\Enums\ContentStatus;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Inertia\Inertia;
use Inertia\Response;

class PagePageController extends Controller
{
    public function show(string $slug): Response
    {
        $page = Page::query()
            ->where('status', ContentStatus::Published)
            ->where('slug', $slug)
            ->firstOrFail();

        return Inertia::render('pages/Show', [
            // ->resolve(), not the bare Resource — see ProgramPageController.
            'page' => (new PageResource($page))->resolve(),
        ]);
    }
}
