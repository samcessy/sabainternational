<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ImageConsentStatus;
use App\Enums\MediaVariantType;
use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\MediaVariant;
use App\Services\AuditLogger;
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
                ->through(function (Media $item) {
                    $thumbnail = $item->variants
                        ->first(fn (MediaVariant $variant) => $variant->variant_type === MediaVariantType::Thumbnail);

                    return [
                        'id' => $item->id,
                        'filename' => $item->filename,
                        'alt_text' => $item->alt_text,
                        'consent_status' => $item->consent_status?->value,
                        'consent_status_label' => $item->consent_status?->label(),
                        'program' => $item->program?->name,
                        'story' => $item->story?->title,
                        'thumbnail_url' => $thumbnail ? Storage::disk('public')->url($thumbnail->path) : null,
                        'created_at' => $item->created_at?->toIso8601String(),
                    ];
                }),
            'imageConsentOptions' => ImageConsentStatus::options(),
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
