<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMediaRequest;
use App\Jobs\GenerateMediaVariants;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

/**
 * Returns JSON, not an Inertia response — same reasoning as
 * DonationController: uploading is a fetch/axios call from within an admin
 * Inertia page (the media library), not a full page visit.
 */
class MediaController extends Controller
{
    public function store(StoreMediaRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $directory = 'media/'.Str::uuid();
        $path = $file->storeAs($directory, "original.{$extension}", 'public');

        $media = Media::create([
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'alt_text' => $request->validated('alt_text'),
            'caption' => $request->validated('caption'),
            'photographer' => $request->validated('photographer'),
            'copyright_license' => $request->validated('copyright_license'),
            'consent_status' => $request->validated('consent_status'),
            'program_id' => $request->validated('program_id'),
            'story_id' => $request->validated('story_id'),
            'focal_point_x' => $request->validated('focal_point_x') ?? 0.5,
            'focal_point_y' => $request->validated('focal_point_y') ?? 0.5,
        ]);

        // Variant generation decodes the file as an image — skip it for a
        // non-image upload (e.g. a PDF annual report) rather than dispatch
        // a job guaranteed to fail. Media::thumbnailUrl() already handles
        // no-variants gracefully everywhere it's called.
        if (str_starts_with($file->getMimeType() ?? '', 'image/')) {
            GenerateMediaVariants::dispatch($media);
        }

        return response()->json(['data' => $media], 201);
    }
}
