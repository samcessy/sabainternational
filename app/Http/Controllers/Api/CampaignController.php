<?php

namespace App\Http\Controllers\Api;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\CampaignResource;
use App\Models\Campaign;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CampaignController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return CampaignResource::collection(
            Campaign::query()
                ->where('status', ContentStatus::Published)
                ->latest('start_date')
                ->paginate(20)
        );
    }

    public function show(string $slug): CampaignResource
    {
        $campaign = Campaign::query()
            ->where('status', ContentStatus::Published)
            ->where('slug', $slug)
            ->firstOrFail();

        return new CampaignResource($campaign);
    }
}
