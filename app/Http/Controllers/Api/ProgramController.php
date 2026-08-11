<?php

namespace App\Http\Controllers\Api;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProgramResource;
use App\Models\Program;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProgramController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ProgramResource::collection(
            Program::query()
                ->where('status', ContentStatus::Published)
                ->orderBy('name')
                ->paginate(20)
        );
    }

    public function show(string $slug): ProgramResource
    {
        $program = Program::query()
            ->where('status', ContentStatus::Published)
            ->where('slug', $slug)
            ->firstOrFail();

        return new ProgramResource($program);
    }
}
