<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ImageConsentStatus;
use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Upload itself is handled by the existing public MediaController::store()
 * (admin/media, saba.md's media-architecture.md §1) - this page's uploader
 * posts there directly rather than duplicating that logic. Only browsing
 * and deletion live here. Editing alt_text/consent_status after upload is
 * deferred - not built yet.
 */
class MediaController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Media::class);

        return Inertia::render('admin/media/Index', [
            'media' => Media::query()
                ->with(['variants', 'program', 'story'])
                ->latest()
                ->paginate(24)
                ->through(fn (Media $item) => [
                    'id' => $item->id,
                    'filename' => $item->filename,
                    'alt_text' => $item->alt_text,
                    'consent_status' => $item->consent_status?->value,
                    'consent_status_label' => $item->consent_status?->label(),
                    'program' => $item->program?->name,
                    'story' => $item->story?->title,
                    'thumbnail_url' => $item->thumbnailUrl(),
                    'created_at' => $item->created_at?->toIso8601String(),
                ]),
            'imageConsentOptions' => ImageConsentStatus::options(),
        ]);
    }

    /**
     * Flat JSON list for the <MediaPicker> component used by Story/
     * TeamMember forms - deliberately not an Inertia page, since it's
     * fetched from inside a Dialog on an already-rendered admin page, not
     * navigated to directly. Capped at the 100 most recent uploads; a
     * proper search/paginated picker is a future improvement once a
     * library this size is a real scenario, not a hypothetical one.
     */
    public function picker(): JsonResponse
    {
        $this->authorize('viewAny', Media::class);

        return response()->json([
            'data' => Media::query()
                ->with('variants')
                ->latest()
                ->limit(100)
                ->get()
                ->map(fn (Media $item) => [
                    'id' => $item->id,
                    'alt_text' => $item->alt_text,
                    'thumbnail_url' => $item->thumbnailUrl(),
                ]),
        ]);
    }

    public function destroy(Media $media): RedirectResponse
    {
        $this->authorize('delete', $media);

        $filename = $media->filename;
        $directory = dirname($media->path);

        $this->auditLogger->log(request()->user(), 'delete', $media, oldValues: $media->only(['filename', 'alt_text']));

        Storage::disk('public')->deleteDirectory($directory);
        $media->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$filename}\" was deleted.",
        ]);

        return back();
    }
}
