<?php

namespace App\Http\Controllers\Api;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\TeamMemberResource;
use App\Models\TeamMember;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TeamController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return TeamMemberResource::collection(
            TeamMember::query()
                ->where('status', ContentStatus::Published)
                ->with('photo')
                ->orderBy('display_order')
                ->paginate(20)
        );
    }
}
