<?php

namespace App\Http\Controllers\Api;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PageController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PageResource::collection(
            Page::query()
                ->where('status', ContentStatus::Published)
                ->latest('published_at')
                ->paginate(20)
        );
    }

    public function show(string $slug): PageResource
    {
        $page = Page::query()
            ->where('status', ContentStatus::Published)
            ->where('slug', $slug)
            ->firstOrFail();

        return new PageResource($page);
    }
}
